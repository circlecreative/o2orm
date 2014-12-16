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
 * @package     O2System
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------
/**
 * O2ORM
 *
 * @package       O2ORM
 * @subpackage
 * @category      Init Class
 * @author        Steeven Andrian Salim
 * @link          http://circle-creative.com/products/o2orm
 */
// ------------------------------------------------------------------------

// Path to base path folder
$orm_path = pathinfo(__FILE__, PATHINFO_DIRNAME).'/';
define('ORMPATH', str_replace("\\", '/', $orm_path));

set_include_path($orm_path);

require_once(ORMPATH.'O2ORM/Config/Constants.php');
require_once(ORMPATH.'O2ORM/Developer.php');

// PSR-0 Autoload
function __autoload($class)
{
    $filename = str_replace('\\','/',$class).__EXT__;
    require_once(ORMPATH.$filename);
}

class O2ORM extends \O2ORM\Initializer
{
    protected static $_instance;

    public static function &initialize()
    {
        $init = new \O2ORM\Initializer();
        return self::$_instance =& $init;
    }
}