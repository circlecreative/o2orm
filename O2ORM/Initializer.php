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
 * Initializer
 *
 * @package       O2ORM
 * @subpackage
 * @category      Core Class
 * @author        Steeven Andrian Salim
 * @link          http://steevenz.com
 * @link          http://circle-creative.com/products/o2orm/user-guide/core/initializer.html
 */
// ------------------------------------------------------------------------

class Initializer
{
    /**
     * Class config
     *
     * @access static protected
     * @var array
     */
    protected static $_config;

    /**
     * Buffered connection flag
     *
     * @access static protected
     * @var bool
     */
    protected static $_is_buffered = FALSE;

    /**
     * Stored schema session flag
     *
     * @access static protected
     * @var bool
     */
    protected static $_is_stored_schema = FALSE;

    /**
     * Get next set of query result row
     *
     * @access static protected
     * @var mixed
     */
    protected static $_conn;

    /**
     * Database Stream
     *
     * @access static public
     * @var O2ORM\Database Object
     */
    public static $db;

    /**
     * Database is connected flag
     *
     * @access static public
     * @var bool
     */
    public static $is_connected = FALSE;

    /**
     * Magic function to access static property
     *
     * @access static public
     * @var O2ORM\Initializer Property
     */
    public function __get($property)
    {
        if(property_exists(__CLASS__,$property))
        {
            return self::$$property;
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Magic function to set static property
     *
     * @access static public
     * @return void
     */
    public function __set($property, $value)
    {
        $property = '_'.$property;
        if(isset(self::$$property))
        {
            self::$$property = $value;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Magic function to call static function
     *
     * @access static public
     * @return O2ORM\Initializer Static Function
     */
    public function __call($function, $args)
    {
        return self::$$function($args);
    }

    // ------------------------------------------------------------------------

    /**
     * Magic function to set static property
     *
     * @access static public
     * @var O2ORM\Database Object
     */
    public static function set($config)
    {
        self::$_config = $config;
    }

    // ------------------------------------------------------------------------

    /**
     * Connect to Database using O2ORM\Database and PDO
     *
     * @access static public
     * @return void
     */
    public static function connect($connection = 'default', $buffered = FALSE)
    {
        if(isset(self::$_config[$connection]))
        {
            if(empty(self::$_conn[$connection]))
            {
                $conn = self::$_config[$connection];
                self::$_conn[$connection] = new Database($conn, $buffered);
            }

            self::$db = self::$_conn[$connection];
            self::$db->charset = @$conn['charset'];
            self::$db->collation = @$conn['collation'];
            self::$db->prefix = @$conn['prefix'];
            self::$db->stored_schema = @$conn['stored_schema'];

            if($conn['stored_schema'] === TRUE)
            {
                self::stored_schema();
            }

            self::$is_connected = self::$db->is_connected;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set stored schema flag
     *
     * @access static public
     * @return void
     */
    public static function stored_schema($stored = TRUE)
    {
        self::$_is_stored_schema = $stored;

        if(self::$_is_stored_schema)
        {
            $fields = \O2ORM\Schema\Structures::$storage;
            self::$db->table->create('system_schema',$fields, array(
                'engine' => 'MYISAM',
                'increment' => TRUE,
                'primary' => 'id',
                'charset' => 'utf8',
                'collate' => 'utf8_unicode_ci',
                'comment' => 'O2ORM Database Information Schema'
            ));
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Create Schema
     *
     * @access static public
     * @return O2ORM\Schema\Mapper
     */
    public static function create($table, $options = array('engine' => 'MYISAM', 'increment' => TRUE, 'primary' => 'AUTO'))
    {
        self::$db->mapper = new \O2ORM\Schema\Mapper();

        if(isset(self::$db->charset))
        {
            $options['charset'] = self::$db->charset;
        }

        if(isset(self::$db->collation))
        {
            $options['collate'] = self::$db->collation;
        }

        if(isset(self::$db->prefix))
        {
            $options['prefix'] = self::$db->prefix;
        }

        self::$db->mapper->create($table, $options);
        return self::$db->mapper;
    }

    // ------------------------------------------------------------------------

    /**
     * Freeze table schema
     *
     * @access static public
     * @return O2ORM\Schema\Mapper
     */
    public static function freeze($table)
    {
        return self::$db->mapper->freeze($table);
    }

    // ------------------------------------------------------------------------

    /**
     * Store Table Schema
     *
     * @access static public
     * @return O2ORM\Schema\Mapper
     */
    public static function store($object)
    {
        return self::$db->mapper->store($object);
    }

    // ------------------------------------------------------------------------

    /**
     * Read data on table
     *
     * @access static public
     * @return O2ORM\Schema\Mapper
     */
    public static function read($table, $conditions)
    {
        if(is_numeric($conditions))
        {
            self::$db->query->from($table)->where(['id' => 1]);
            $row = self::$db->row();
            $row->table = $table;
            return $row;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Update data on table
     *
     * @access static public
     * @return O2ORM\Schema\Mapper
     */
    public static function update($object)
    {
        $table = $object->table;
        $id = $object->id;
        unset($object->table, $object->id);
        self::$db->update($table, get_object_vars($object), ['id' => $id]);
    }

    // ------------------------------------------------------------------------

    /**
     * Delete data on table
     *
     * @access static public
     * @return void
     */
    public static function delete($object)
    {
        $table = $object->table;
        self::$db->delete($table, ['id' => $object->id]);
    }

    // ------------------------------------------------------------------------

    /**
     * Close database connection
     *
     * @access public
     * @return void
     */
    public function close()
    {
        self::$is_connected = FALSE;
        self::$db->close();
    }
}

/* End of file Initializer.php */
/* Location: ./O2ORM/Initializer.php */