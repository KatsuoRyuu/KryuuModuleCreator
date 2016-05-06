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

namespace KryuuModuleCreator\Prompt;

use Zend\Console\Prompt\Line;
use Zend\Console\Console;
use Zend\Console\ColorInterface as Color;

/**
 * @project Ryuu-ZF2
 * @authors spawn
 * @encoding UTF-8
 * @date Apr 5, 2016 - 2:52:45 PM
 * @package *
 * @todo *
 * @depends *
 * @note *
 */

class ConfirmLine extends Line
{
    public static function ePrompt($promptText = 'Please enter value: ', $default = false, $x = 0, $y = 0)
    {
        if ($default == true) {
            $defaultText = '[Y/n]';
        } else {
            $defaultText = '[y/N]'; 
        }
        $console = Console::getInstance();
        $console->setColor(Color::BLACK);
        $console->setBgColor(Color::WHITE);
        $console->setPos($x, $y);
        $line = new Line($promptText . $defaultText, true, 10);
        $answer = $line->show();
        if (strtolower(trim($answer)) == 'y') {
            $answer = true;
        } else if (strtolower(trim($answer)) == 'n') {
            $answer = false;
        } else if (strtolower(trim($answer)) == '') {
            $answer = $default;
        } else {
            $console->setPos($x, $y);
            $console->write($promptText . $defaultText . str_repeat(' ', strlen($answer)+2));
            return ConfirmLine::ePrompt($promptText, $default, $x, $y);
        }
        return $answer;
    }
}
