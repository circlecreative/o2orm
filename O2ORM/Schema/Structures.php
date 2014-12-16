<?php
namespace O2ORM\Schema;
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
 * @since       Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined('ORMPATH') OR exit('No direct script access allowed');

/**
 * Abstracts Schema Structures Class
 *
 * @package     O2ORM
 * @subpackage  Schema
 * @category    Schema Class
 * @author      Steeven Andrian Salim
 * @link        http://circle-creative.com/products/o2orm/user-guide/schema/structures.html
 */
// ------------------------------------------------------------------------

abstract class Structures
{
    /**
     * Default Self Hierarchical Table Schema
     *
     * @access static public
     * @var array
     */
    public static $self_hierarchical = array(
        'id' => ['int',11,'not-null','0'],
        'parent_id' => ['int',11,'not-null','0'],
        'created_date' => ['datetime','null'],
        'created_user' => ['int',11,'null'],
        'modified_date' => ['timestamp','null'],
        'modified_user' => ['int',11,'null'],
        'checkin_date' => ['datetime','null'],
        'checkin_user' => ['int',11,'null'],
        'status' => ['tinyint',1,'not-null','default:1'],
        'ordering' => ['int',11,'null'],
        'lft' => ['int',11,'null'],
        'rgt' => ['int',11,'null'],
        'dpt' => ['int',11,'null'],
    );

    // ------------------------------------------------------------------------

    /**
     * Default Table Schema
     *
     * @access static public
     * @var array
     */
    public static $default = array(
        'id' => ['int',11,'not-null','primary','default:1'],
        'created_date' => ['datetime','null','default:timestamp'],
        'created_user' => ['int',11,'null','default:0'],
        'modified_date' => ['timestamp','null','default:timestamp'],
        'modified_user' => ['int',11,'null','default:0'],
        'checkin_date' => ['datetime','null','default:null-timestamp'],
        'checkin_user' => ['int',11,'null','default:0'],
        'status' => ['tinyint',1,'not-null','default:1'],
        'ordering' => ['int',11,'null','default:1'],
    );

    // ------------------------------------------------------------------------

    /**
     * Default Table Schema Storage
     *
     * @access static public
     * @var array
     */
    public static $storage = array(
        'id' => ['int',11,'not-null','primary'],
        'parent_id' => ['int',11,'not-null'],
        'table' => ['varchar',255,'null'],
        'description' => ['text','null'],
        'engine' => ['varchar',25,'null'],
        'charset' => ['varchar',25,'null'],
        'collation' => ['varchar',25,'null'],
        'fields' => ['text','null'],
        'primary_keys' => ['varchar',255,'null'],
        'relations' => ['text','null'],
        'params' => ['text','null'],
        'structures' => ['longtext','null'],
        'metadata' => ['longtext','null'],
        'sql' => ['text','null'],
        'size' => ['int',11,'null'],
        'created_date' => ['datetime','null'],
        'updated_date' => ['datetime','null'],
        'modified_date' => ['timestamp','null']
    );
}

/* End of file Structures.php */
/* Location: ./O2ORM/Structures.php */