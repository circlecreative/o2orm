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
 * ORM Validate Class
 *
 * Validate type of value
 *
 * @package		O2ORM
 * @subpackage
 * @category	Core Class
 * @author		Steeven Andrian Salim
 * @link        http://steevenz.com
 * @link		http://circle-creative.com/products/o2orm/user-guide/core/validate.html
 */
// ------------------------------------------------------------------------

class Validate
{
    /**
     * Tests if an input is valid PHP serialized string.
     *
     * Checks if a string is serialized using quick string manipulation
     * to throw out obviously incorrect strings. Unserialize is then run
     * on the string to perform the final verification.
     *
     * @author		Chris Smith <code+php@chris.cs278.org>
     * @copyright	Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
     * @license		http://sam.zoy.org/wtfpl/ WTFPL
     * @param		string	$value	Value to test for serialized form
     * @param		mixed	$result	Result of unserialize() of the $value
     * @return		boolean			True if $value is serialized data, otherwise false
     */
    public static function is_serialized($value, &$result = null)
    {
        // Bit of a give away this one
        if (!is_string($value))
        {
            return false;
        }

        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;')
        {
            $result = false;
            return true;
        }

        $length	= strlen($value);
        $end	= '';

        if(! isset($value[0])) return false;

        switch ($value[0])
        {
            case 's':
                if ($value[$length - 2] !== '"')
                {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';

                if ($value[1] !== ':')
                {
                    return false;
                }

                switch ($value[2])
                {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;

                    default:
                        return false;
                }
            case 'N':
                $end .= ';';

                if ($value[$length - 1] !== $end[0])
                {
                    return false;
                }
                break;

            default:
                return false;
        }

        if (($result = @unserialize($value)) === false)
        {
            $result = null;
            return false;
        }
        return true;
    }

    // ------------------------------------------------------------------------

    /**
     * Checks if string is valid json.
     *
     * @author Andreas Glaser
     *
     * @param $string
     * @return bool
     */
    public static function is_json($string)
    {
        // make sure provided input is of type string
        if (!is_string($string))
        {
            return false;
        }

        // trim white spaces
        $string = trim($string);

        // get first character
        $first_char = substr($string, 0, 1);

        // get last character
        $last_char = substr($string, -1);

        // check if there is a first and last character
        if (!$first_char || !$last_char)
        {
            return false;
        }

        // make sure first character is either { or [
        if ($first_char !== '{' && $first_char !== '[')
        {
            return false;
        }

        // make sure last character is either } or ]
        if ($last_char !== '}' && $last_char !== ']')
        {
            return false;
        }

        // let's leave the rest to PHP.
        // try to decode string
        json_decode($string);

        // check if error occurred
        $is_valid = json_last_error() === JSON_ERROR_NONE;

        return $is_valid;
    }

    // ------------------------------------------------------------------------
}

/* End of file Validate.php */
/* Location: ./O2ORM/Validate.php */