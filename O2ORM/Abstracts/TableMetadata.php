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
 * Table Metadata Abstracts Class
 *
 * @package     O2ORM
 * @subpackage  Abstracts
 * @category    Abstracts Class
 * @author      Steeven Andrian Salim
 * @link        http://steevenz.com
 * @link        http://circle-creative.com/products/o2orm/user-guide/abstracts/table-metadata.html
 */
// ------------------------------------------------------------------------

abstract class TableMetadata
{
    public $name;
    public $engine;
    public $version;
    public $rows;
    public $auto_increment = 1;
    public $created_time;
    public $updated_date;
    public $charset = 'utf8';
    public $collaction = 'utf8_unicode_ci';
    public $fields;
    public $num_fields = 0;
    public $primary_keys;
    public $foreign_keys;
    public $indexes;
    public $triggers;
    public $comments;
    public $sql_builder;
    public $sql_samples;
}

/* End of file TableMetadata.php */
/* Location: ./O2ORM/Interfaces/TableMetadata.php */