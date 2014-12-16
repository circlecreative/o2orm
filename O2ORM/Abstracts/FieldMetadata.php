<?php
namespace O2ORM\Abstracts;
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
 * Field Metadata Abstracts Class
 *
 * @package     O2ORM
 * @subpackage  Abstracts
 * @category    Abstracts Class
 * @author      Steeven Andrian Salim
 * @link        http://steevenz.com
 * @link        http://circle-creative.com/products/o2orm/user-guide/abstracts/field-metadata.html
 */
// ------------------------------------------------------------------------

abstract class FieldMetadata 
{
    public $table;
    public $name;
    public $tick_name;
    public $param_name;
    public $alias_name;
    public $type;
    public $max_length;
    public $null = 'YES';
    public $primary_key = 'NO';
    public $foreign_key = 'NONE';
    public $references = 'NONE';
    public $constraint = 'NO';
    public $constraint_name = 'NONE';
    public $indexes = 'NO';
    public $indexes_name = 'NONE';
}

/* End of file FieldMetadata.php */
/* Location: ./O2ORM/Interfaces/FieldMetadata.php */