<?php
namespace O2ORM;
    /**
     * O2ORM
     *
     * An open source Database Framework for PHP 5.2.4 or newer
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
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
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     *
     * @package     O2ORM
     * @author      Steeven Andrian Salim
     * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
     * @license     http://circle-creative.com/products/o2system/license.html
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @link        http://circle-creative.com
     * @since       Version 2.0
     * @filesource
     */

// ------------------------------------------------------------------------
defined('O2ORM_PATH') OR exit('No direct script access allowed');
/**
 * Result Builder Class
 *
 * @package     O2System
 * @subpackage  system/core
 * @category    Core Class
 * @author      Steeven Andrian Salim
 * @link        http://circle-creative.com/products/o2orm/user-guide/result.html
 */
// ------------------------------------------------------------------------
class Metadata
{
    /**
     * Metadata type flag
     *
     * @access private
     * @var string
     */
    private static $metatype = 'default';

    /**
     * List of table indexes keys
     *
     * @access private
     * @var array
     */
    private static $list_indexes_keys = array(
        'key_name','column_name'
    );

    /**
     * Set metadata type
     *
     * @access public
     * @return void
     */
    public static function set_type($type = 'default')
    {
        self::$metatype = $type;
    }

    /**
     * Global setting metadata indexes
     *
     * @access public
     * @var void
     */
    public function __set($name, $value)
    {
        switch(self::$metatype)
        {
            default:
                $name = strtolower($name);
                $this->{$name} = $value;
                break;

            case 'indexes':
                $this->__indexes($name, $value);
                break;

            case 'list_indexes':
                $this->__list_indexes($name, $value);
                break;
        }
    }

    /**
     * Standard metadata indexes
     *
     * @access public
     * @var void
     */
    private function __indexes($name, $value)
    {
        $name = strtolower($name);

        if ($name === 'type') {
            $x_value = explode('(', str_replace(')', '', $value));

            if (count($x_value) > 1) {
                $this->{$name} = strtoupper(reset($x_value));
                $this->max_length = end($x_value);
            } else {
                $this->{$name} = strtoupper($value);
                $this->max_length = 0;
            }
        } elseif($name == 'field') {
            $this->name = $value;
        }
        elseif($name == 'key') {
            $this->primary_key = ($value === 'PRI' ? TRUE : FALSE);
        } else {
            $this->{$name} = $value;
        }
    }

    /**
     * List metadata table indexes
     *
     * @access public
     * @var void
     */
    private function __list_indexes($name, $value)
    {
        $name = strtolower($name);
        if(in_array($name, self::$list_indexes_keys))
        {
            $this->{$name} = $value;
        }
    }
}