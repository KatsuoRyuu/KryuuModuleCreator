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

namespace KryuuModuleCreator\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use KryuuModuleCreator\Prompt\ConfirmLine;
use KryuuModuleCreator\Prompt\ExSelect;
use KryuuModuleCreator\Template\Builder;
use KryuuTemplateBuilder\Service\TemplateServiceInterface as TemplateServiceInterface;
use KryuuModuleCreator\View\Frame;

/**
 * @project Ryuu-ZF2
 * @authors spawn
 * @encoding UTF-8
 * @date Mar 15, 2016 - 2:28:40 AM
 * @package *
 * @todo *
 * @depends *
 * @note *
 */

class ConsoleController extends AbstractActionController
{    
    private $console = null;
    private $templatesBuilder = null;
    
    public function __construct(TemplateServiceInterface $templateBuilder)
    {
        $this->setTemplateBuilder($templateBuilder);
    }
    
    public function allAction()
    {   
        $frame = new Frame();
        $frame->setTemplateService($this->getTemplateBuilder());
        $template = $frame->show();
        $package = $this->getTemplateBuilder()->build($template);
        $this->getTemplateBuilder()->install($package);
    }
    
    public function allAction1()
    {      
        $xPlacement = (int) ($this->console->getWidth()/3);
        
        $this->clearScreen();
        $this->mkHeadline("please select the template");
        $aTmpl = $this->getTemplates();
        $selector = new ExSelect(
            'Enter Choose template [0]',
            $aTmpl,
            true,
            true
        );
        
        $answer = $selector->setPos($xPlacement, 6)
                ->setBgColor(Color::WHITE)
                ->setFgColor(Color::BLACK)
                ->show();
                
        if (trim($answer) == '') {
            $answer = 0;
        }
        $this->console->setColor(Color::BLACK);
        $this->console->setBgColor(Color::WHITE);
        $this->console->setPos(6, (count($aTmpl) + 8));
        if (!ConfirmLine::ePrompt($aTmpl[$answer] . ' correct?', true, $xPlacement, (count($aTmpl) + 10))) {
           return $this->allAction();
        }
        
        $template = $this->getTemplate($aTmpl[$answer]);
        foreach ($template as $key => $section) {
            $this->clearScreen();
            $this->mkHeadline($section['headline']);
            $config = $this->makeQuestions($section['config'], $xPlacement, 6);
            $template[$key]['config'] = $config;
        }
        $builder = new Builder();
        $builder->setTemplate($template)
                ->setTemplatePath($this->templates[$aTmpl[$answer]]);
        $builder->build();
    }
    
    private function readTemplate()
    {
        
    }
    
    /**
     * 
     * @param \KryuuTemplateBuilder\Service\TemplateServiceInterface $templateBuilder
     */
    private function setTemplateBuilder($templateBuilder)
    {
        $this->templatesBuilder = $templateBuilder;
    }
    
    /**
     * 
     * @return \KryuuTemplateBuilder\Service\TemplateServiceInterface
     */
    private function getTemplateBuilder()
    {
        return $this->templatesBuilder;
    }
}
