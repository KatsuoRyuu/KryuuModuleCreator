<?php

/* 
 * @license The Ryuu Technology License
 * 
 * Copyright 2014 Ryuu Technology by
 * KatsuoRyuu <anders-github@drake-development.org>.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * Ryuu Technology shall be visible and readable to anyone using the software
 * and shall be written in one of the following ways: 竜技術, Ryuu Technology
 * or by using the company logo.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * 
 * @link https://github.com/KatsuoRyuu/
 */

namespace KryuuModuleCreator\View;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Adapter\AdapterInterface as Console;
use KryuuModuleCreator\Prompt\ConfirmLine;
use KryuuModuleCreator\Prompt\ExSelect;
use KryuuModuleCreator\Template\Builder;
use KryuuTemplateBuilder\Service\TemplateServiceInterface as TemplateServiceInterface;
use KryuuCoolConsole\View\Grid;
use KryuuCoolConsole\Container\Box;
use KryuuCoolConsole\Control\Input;
use KryuuCoolConsole\Control\ChoiceBool;
use KryuuCoolConsole\Control\ChoiceList;
use KryuuCoolConsole\Container\BoxInterface as Border;
use KryuuCoolConsole\Charset\ColorInterface as Color;
use KryuuTemplateBuilder\Entity\Template\QuestionInterface;
use KryuuTemplateBuilder\Entity\Template\GroupInterface;
use KryuuTemplateBuilder\Entity\Template\VariableInterface;
use KryuuTemplateBuilder\Entity\Template\TemplateInterface;
/**
 * @project Ryuu-ZF2
 * @authors spawn
 * @encoding UTF-8
 * @date May 5, 2016 - 6:21:54 PM
 * @package *
 * @todo *
 * @depends *
 * @note *
 */

Class Frame
{
    private $templateService;
    private $groups = [];
    private $currentGroup = null;
    private $currentView = null;
    
    private $inputs = [];
    private $questions = [];
    
    public function setTemplateService(TemplateServiceInterface $templateService)
    {
        $this->templateService = $templateService;
    }
    
    public function show()
    {	
        $templateChoice = $this->selectTemplate();
        $template = $this->getTemplateService()->getTemplate($templateChoice);
        $this->templateQuestionair($template);
        return $template;
    }
    
    public function selectTemplate()
    {
        
        $view = new Grid();
        $box = new Box(1,1, Border::BORDER_DOUBLE);
        $box->setBgColor(Color::BC_MARINE);
        
        $choiseList = new ChoiceList('template-select');
        $templateList = $this->getTemplateService()->getTemplateList();
        $choiseList->setItems($templateList);
        $choiseList->setHeadline('Select The template to use');
             
        $box->addChild($choiseList);
        $view->addChild($box);
        $view->show();
        
        foreach ($templateList as $key => $value) {
            if($choiseList->getValue() == $value) {
                return $templateList[$key];
            }
        }
        
        return false;
    }
    
    private function templateQuestionair($template)
    {
        $this->reader($template);
        
        if ($this->currentGroup != null) {
            $this->groups[] = $this->currentGroup;
        }
        foreach ($this->groups as $group) {
            $group->show();
        }
        foreach ($this->inputs as $input) {
            $this->fillQuestion($this->questions[$input->getId()], $input->getValue());
        }
    }
    
    private function reader($contents)
    {        
        $contents = is_array($contents) ? $contents : [$contents];
        foreach ($contents as $content) {
            if ($content instanceof GroupInterface) {
                $this->group($content);
            } elseif ($content instanceof TemplateInterface) {
                $this->reader($content->getAll());
            } elseif ($content instanceof QuestionInterface) {
                $this->readQuestion($content);
            } elseif ($content instanceof FileInterface) {
                $this->readFile($content);
            }
        }
    }
    
    private function readQuestion(QuestionInterface $question)
    {
        if ($question->getType() == 'boolean' || $question->getType() == 'bool') {
            $choice = new ChoiceBool($question->getText());
        } elseif ($question->getType() == 'varchar') {
            $choice = new input($question->getText());
        }
        $this->inputs[] = $choice;
        $this->questions[$choice->getId()] = $question;
        $this->getCurrentView()->addChild($choice);
    }
    
    private function fillQuestion(QuestionInterface $question, $value)
    {
        if ($question->getType() == 'boolean' || $question->getType() == 'bool') {
            $question->setDefault($value);
        } elseif ($question->getType() == 'varchar') {
            if (empty($value) || trim($value) == '') {
                $question->setTemplates($question->getDefault());
            } else {
                $default = $question->getDefault();
                $default[0]->setContent($value);
                $question->setTemplates($default);
            }
        }
    }
    
    private function group($group)
    {
        if ($this->currentGroup != null) {
            $this->groups[] = $this->currentGroup;
        }
        $view = new Grid();
        $box = new Box(1, 1, Border::BORDER_DOUBLE);
        $box->setBgColor(Color::BC_MARINE);
        $view->addChild($box);
        $this->currentGroup = $view; 
        $this->currentView = $box;
        $this->reader($group->getAll());
    }
    
    private function getCurrentView()
    {
        if ($this->currentView == null) {
            if ($this->currentGroup != null) {
                $this->groups[] = $this->currentGroup;
            }
            $view = new Grid();
            $box = new Box(1, 1, Border::BORDER_DOUBLE);
            $view->addChild($box);
            $this->currentGroup = $view; 
            $this->currentView = $box;
        }
        
        return $this->currentView;
    }
    /**
     * 
     * @return \KryuuTemplateBuilder\Service\TemplateServiceInterface
     */
    private function getTemplateService()
    {
        return $this->templateService;
    }
    
}
