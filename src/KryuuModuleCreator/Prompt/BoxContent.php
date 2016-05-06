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

/**
 * @project Ryuu-ZF2
 * @authors spawn
 * @encoding UTF-8
 * @date Apr 5, 2016 - 3:29:01 PM
 * @package *
 * @todo *
 * @depends *
 * @note *
 */


class BoxContent 
{

    /**
     * Write a box at the specified coordinates.
     * If X or Y coordinate value is negative, it will be calculated as the distance from far right or bottom edge
     * of the console (respectively).
     *
     * @param int      $x1           Top-left corner X coordinate (column)
     * @param int      $y1           Top-left corner Y coordinate (row)
     * @param int      $x2           Bottom-right corner X coordinate (column)
     * @param int      $y2           Bottom-right corner Y coordinate (row)
     * @param int      $lineStyle    (optional) Box border style.
     * @param int      $fillStyle    (optional) Box fill style or a single character to fill it with.
     * @param int      $color        (optional) Foreground color
     * @param int      $bgColor      (optional) Background color
     * @param null|int $fillColor    (optional) Foreground color of box fill
     * @param null|int $fillBgColor  (optional) Background color of box fill
     * @throws Exception\BadMethodCallException if coordinates are invalid
     */
    public function writeBox(
        $x1,
        $y1,
        $x2,
        $y2,
        $lineStyle = self::LINE_SINGLE,
        $fillStyle = self::FILL_NONE,
        $color = null,
        $bgColor = null,
        $fillColor = null,
        $fillBgColor = null
    ) {
        // Sanitize coordinates
        $x1 = (int) $x1;
        $y1 = (int) $y1;
        $x2 = (int) $x2;
        $y2 = (int) $y2;

        // Translate negative coordinates
        if ($x2 < 0) {
            $x2 = $this->getWidth() - $x2;
        }

        if ($y2 < 0) {
            $y2 = $this->getHeight() - $y2;
        }

        // Validate coordinates
        if ($x1 < 0
            || $y1 < 0
            || $x2 < $x1
            || $y2 < $y1
       ) {
            throw new Exception\BadMethodCallException('Supplied X,Y coordinates are invalid.');
        }

        // Determine charset and dimensions
        $charset = $this->getCharset();
        $width   = $x2 - $x1 + 1;

        if ($width <= 2) {
            $lineStyle = static::LINE_NONE;
        }

        // Activate line drawing
        $this->write($charset::ACTIVATE);

        // Draw horizontal lines
        if ($lineStyle !== static::LINE_NONE) {
            switch ($lineStyle) {
                case static::LINE_SINGLE:
                    $lineChar = $charset::LINE_SINGLE_EW;
                    break;

                case static::LINE_DOUBLE:
                    $lineChar = $charset::LINE_DOUBLE_EW;
                    break;

                case static::LINE_BLOCK:
                default:
                    $lineChar = $charset::LINE_BLOCK_EW;
                    break;
            }

            $this->setPos($x1 + 1, $y1);
            $this->write(str_repeat($lineChar, $width - 2), $color, $bgColor);
            $this->setPos($x1 + 1, $y2);
            $this->write(str_repeat($lineChar, $width - 2), $color, $bgColor);
        }

        // Draw vertical lines and fill
        if (is_numeric($fillStyle)
            && $fillStyle !== static::FILL_NONE) {
            switch ($fillStyle) {
                case static::FILL_SHADE_LIGHT:
                    $fillChar = $charset::SHADE_LIGHT;
                    break;
                case static::FILL_SHADE_MEDIUM:
                    $fillChar = $charset::SHADE_MEDIUM;
                    break;
                case static::FILL_SHADE_DARK:
                    $fillChar = $charset::SHADE_DARK;
                    break;
                case static::FILL_BLOCK:
                default:
                    $fillChar = $charset::BLOCK;
                    break;
            }
        } elseif ($fillStyle) {
            $fillChar = StringUtils::getWrapper()->substr($fillStyle, 0, 1);
        } else {
            $fillChar = ' ';
        }

        if ($lineStyle === static::LINE_NONE) {
            for ($y = $y1; $y <= $y2; $y++) {
                $this->setPos($x1, $y);
                $this->write(str_repeat($fillChar, $width), $fillColor, $fillBgColor);
            }
        } else {
            switch ($lineStyle) {
                case static::LINE_DOUBLE:
                    $lineChar = $charset::LINE_DOUBLE_NS;
                    break;
                case static::LINE_BLOCK:
                    $lineChar = $charset::LINE_BLOCK_NS;
                    break;
                case static::LINE_SINGLE:
                default:
                    $lineChar = $charset::LINE_SINGLE_NS;
                    break;
            }

            for ($y = $y1 + 1; $y < $y2; $y++) {
                $this->setPos($x1, $y);
                $this->write($lineChar, $color, $bgColor);
                $this->write(str_repeat($fillChar, $width - 2), $fillColor, $fillBgColor);
                $this->write($lineChar, $color, $bgColor);
            }
        }

        // Draw corners
        if ($lineStyle !== static::LINE_NONE) {
            if ($color !== null) {
                $this->setColor($color);
            }
            if ($bgColor !== null) {
                $this->setBgColor($bgColor);
            }
            if ($lineStyle === static::LINE_SINGLE) {
                $this->writeAt($charset::LINE_SINGLE_NW, $x1, $y1);
                $this->writeAt($charset::LINE_SINGLE_NE, $x2, $y1);
                $this->writeAt($charset::LINE_SINGLE_SE, $x2, $y2);
                $this->writeAt($charset::LINE_SINGLE_SW, $x1, $y2);
            } elseif ($lineStyle === static::LINE_DOUBLE) {
                $this->writeAt($charset::LINE_DOUBLE_NW, $x1, $y1);
                $this->writeAt($charset::LINE_DOUBLE_NE, $x2, $y1);
                $this->writeAt($charset::LINE_DOUBLE_SE, $x2, $y2);
                $this->writeAt($charset::LINE_DOUBLE_SW, $x1, $y2);
            } elseif ($lineStyle === static::LINE_BLOCK) {
                $this->writeAt($charset::LINE_BLOCK_NW, $x1, $y1);
                $this->writeAt($charset::LINE_BLOCK_NE, $x2, $y1);
                $this->writeAt($charset::LINE_BLOCK_SE, $x2, $y2);
                $this->writeAt($charset::LINE_BLOCK_SW, $x1, $y2);
            }
        }

        // Deactivate line drawing and reset colors
        $this->write($charset::DEACTIVATE);
        $this->resetColor();
    }
}
