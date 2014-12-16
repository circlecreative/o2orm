<?php
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
 * System Developer
 *
 * Add developer functions and class.
 *
 * @package		O2ORM
 * @subpackage
 * @category	Developer Helper
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2orm/user-guide/core/developer.html
 */
// ------------------------------------------------------------------------

/**
 * Developer print out
 *
 * Print out string, integer, boolean, array and object
 * perlines with line number and tracing backtrace
 *
 * @access public
 */
if (!function_exists('print_out'))
{
    function print_out($data = '', $trace = TRUE, $die = TRUE)
    {
        if(is_bool($data))
        {
            $data = $data === TRUE ? 'TRUE' : 'FALSE';
        }

        if(empty($data))
        {
            $data = 'Output is empty';
        }

        $data = print_r($data,TRUE);
        $data = htmlentities($data);
        $data = htmlspecialchars( htmlspecialchars_decode($data, ENT_QUOTES), ENT_QUOTES, 'UTF-8' );
        $data = str_replace("&nbsp;", "", $data);
        $data = trim($data);

        if($trace == TRUE) $tracer = print_trace();

        ob_start();

        // Load print out template
        include ORMPATH . 'O2ORM/Views/developer/print_out' . __EXT__;
        $buffer = ob_get_contents();
        ob_end_clean();

        echo $buffer;

        // Die
        if($die) die();
    }
}

// ------------------------------------------------------------------------

/**
 * Developer print line
 *
 * Print out string, integer, boolean, array and object
 * perlines with line number and tracing backtrace
 *
 * @access public
 */
if(! function_exists('print_line'))
{
    function print_line($line = '', $end_line = FALSE, $trace = TRUE, $die = TRUE)
    {
        if (function_exists('session_status') AND session_status() == PHP_SESSION_NONE OR session_id() == '')
        {
            session_start();
        }

        $line = print_r($line, TRUE);
        $line = str_replace("&nbsp;", "", $line);
        $line = trim($line);

        $_SESSION['O2DeveloperPrintLines'][] = $line;

        if($end_line === TRUE OR strtoupper($line) === 'ENDLINE' OR $line === '---')
        {
            $data = '';
            $data = implode(PHP_EOL, array_unique($_SESSION['O2DeveloperPrintLines']));

            session_destroy();

            print_out($data, $trace, $die);
        }
        elseif(strtoupper($end_line) === 'FLUSH')
        {
            unset($_SESSION['O2DeveloperPrintLines']);

            $_SESSION['O2DeveloperPrintLines'][] = $line;
        }
    }
}

// ------------------------------------------------------------------------

/**
 * Developer marker for working with print line
 *
 * Print out string, integer, boolean, array and object
 * perlines with line number and tracing backtrace
 *
 * @access public
 */
if (! function_exists('print_line_marker'))
{
    function print_line_marker($comment, $mark = 'START', $repeat = 5, $splitter = '-')
    {
        static $_comment;
        static $_repeat;
        static $_splitter;

        if($mark === 'START' AND $comment !== 'END')
        {
            $_comment = $comment;
            $_repeat = $repeat;
            $_splitter = $splitter;
        }

        if($comment === 'END') $mark = $comment;

        print_line(str_repeat($_splitter, $_repeat).' '.$_comment.' '.str_repeat($_splitter, $_repeat).' '.$mark);
    }
}

// ------------------------------------------------------------------------

/**
 * Developer print trace
 *
 * Debug Backtrace Printer
 *
 * @access public
 */
if (! function_exists('print_trace'))
{
    function print_trace()
    {
        static $script_start = 0;

        list($usec, $sec) = explode(' ', microtime());
        $script_start = (float) $sec + (float) $usec;

        $trace = debug_backtrace();
        $trace = array_slice($trace, 1);
        $trace = array_reverse($trace);

        $output =  '<ol>';
        $i=0;
        foreach($trace as $line)
        {
            $output.= '<li>';
            $code = (empty($line['class']) ? $line['function'].'()' : $line['class'].'->'.$line['function'].'()');
            $output.= '<i class="fa fa-code"></i> <strong>'.$code.'</strong><button rel="trace-args-'.$i.'" class="toggle-args btn btn-xs pull-right">Toggle Arguments</button><br />';
            $output.= '<i class="fa fa-file-code-o"></i> '.@$line['file'].'<br />';


            list($usec, $sec) = explode(' ', microtime());
            $script_end = (float) $sec + (float) $usec;
            $elapsed_time = round($script_end - $script_start, 5);

            $output.= '<i class="fa fa-list-ol"></i> '.@$line['line'].' / <i class="fa fa-clock-o"></i> '.pow(10, $elapsed_time).' seconds / <i class="fa fa-dashboard"></i> '. (memory_get_usage() / 1000000) . 'MB<br>';
            $output.= '<hr>';
            if(!empty($line['args']))
            {
                $output.= '<pre id="trace-args-'.$i.'" style="display:none;">';
                $output.= print_r($line['args'], TRUE);
                $output.= '</pre>';
            }

            $output.= '</li>';
            $i++;
        }
        $output.= '</ol>';

        list($usec, $sec) = explode(' ', microtime());
        $script_end = (float) $sec + (float) $usec;
        $elapsed_time = round($script_end - $script_start, 5);

        $output.= '<span style="color:#666">';
        $output.= '<i class="fa fa-clock-o"></i> ' . pow(10,  $elapsed_time) . ' seconds / ';
        $output.= '<i class="fa fa-dashboard"></i> ' . memory_get_usage() / 1000000 . ' MB';
        $output.= '</span>';

        return $output;
    }
}

// ------------------------------------------------------------------------


/**
 * Standard var_dump with pre tag
 *
 * @access public
 */
if (! function_exists('print_dump'))
{
    function print_dump($data, $die = TRUE)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';

        // Die
        if($die) die();
    }
}

// ------------------------------------------------------------------------

/**
 * Assets URL for Developer views file
 *
 * @access public
 */
if (!function_exists('assets_url'))
{
    function assets_url()
    {
        if (isset($_SERVER['HTTP_HOST']))
        {
            $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $base_url .= '://' . $_SERVER['HTTP_HOST'];
            $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        }
        else
        {
            $base_url = 'http://localhost/';
        }

        return $base_url . 'O2ORM/Views/Assets/';
    }
}

// ------------------------------------------------------------------------

/**
 * Print output to browser console with javascript
 *
 * @access public
 */
if (! function_exists('print_console'))
{
    function print_console($data)
    {
        if(is_array($data) OR is_object($data))
        {
            echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
        }
        else
        {
            echo("<script>console.log('PHP: ".$data."');</script>");
        }
    }
}

// ------------------------------------------------------------------------

/**
 * Standard print code with pre tag
 * Print string, integer, boolean, array, object, html and etc.
 *
 * @access public
 */
if (! function_exists('print_code'))
{
    function print_code($data, $die = TRUE)
    {
        echo '<pre>';
        $data = print_r($data,TRUE);
        $data = htmlentities($data);
        $data = htmlspecialchars( htmlspecialchars_decode($data, ENT_QUOTES), ENT_QUOTES, 'UTF-8' );
        $data = str_replace("&nbsp;", "", $data);
        $data = trim($data);
        echo $data;
        echo '</pre>';

        // Die
        if($die) die();
    }
}

/* End of file Developer.php */
/* Location: .O2ORM/Developer.php */