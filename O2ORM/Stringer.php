<?php
namespace O2ORM;
/**
 * O2ORM
 *
 * An open source ORM Database Framework for PHP 5.2.4 or newer
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
 * @since       Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined('ORMPATH') OR exit('No direct script access allowed');

/**
 * String conversion class
 *
 * @package		O2ORM
 * @subpackage
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link		http://steevenz.com/
 * @link		http://circle-creative.com/products/o2orm/user-guide/core/stringer.html
 */
// ------------------------------------------------------------------------

class Stringer
{
    public static function prepare($string, $lowercase = TRUE)
    {
        if (! empty($string))
        {
            $patterns = array(
                '/[\s]+/',
                '/[^a-zA-Z0-9_-\s]/',
                '/[_]+/',
                '/[-]+/',
                '/-/',
                '/[_]+/'
            );
            $replace = array(
                '-',
                '-',
                '-',
                '-',
                '_',
                '_'
            );
            $string = preg_replace($patterns, $replace, $string);
            $string = $lowercase === TRUE ? strtolower($string) : $string;
            return trim($string);
        }
        return false;
    }

    // ------------------------------------------------------------------------

    public static function flatten($array, $quote = TRUE)
    {
        if(! empty($array))
        {
            foreach($array as $key => $value)
            {
                $flatten[] = $quote === TRUE ? "'".$value."'" : $value;
            }

            return implode(',',$flatten);
        }

        return NULL;
    }

    // ------------------------------------------------------------------------

    public static function quote($string)
    {
        if(is_numeric($string))
        {
            return $string;
        }
        elseif(is_string($string))
        {
            return "'".$string."'";
        }
    }


    public static function escape($string, $lowercase = TRUE)
    {
        $string = trim($string);

        if (strpos($string, '.') !== FALSE)
        {
            $string = explode('.', $string);

            $string = array_map(
                function($string)
                {
                    return '`'.trim($string).'`';
                }, $string
            );

            $string = implode('.', $string);
        }
        else
        {
            $string = '`'.$string.'`';
        }

        return ($lowercase === TRUE ? strtolower($string) : $string);
    }
}

/* End of file Stringer.php */
/* Location: ./O2ORM/Stringer.php */