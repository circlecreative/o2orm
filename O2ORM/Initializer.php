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
 * @package     O2System
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
 * Active Record Class
 *
 * @package		O2System
 * @subpackage	system/core
 * @category	Developer
 * @author		Steeven Andrian Salim
 * @link		http://circle-creative.com/products/o2orm/user-guide/active-records.html
 */

// ------------------------------------------------------------------------

class Initializer
{
    /**
     * PDO connection flag
     *
     * @access public
     * @var bool
     */
    public static $is_connected = FALSE;
    /**
     * PDO connection stream
     *
     * @access public
     * @var \PDO Object
     */
    public static $pdo = NULL;
    /**
     * O2ORM Builder Object
     *
     * @access public
     * @var \O2ORM\Builder Object
     */
    public static $builder = NULL;
    /**
     * Active connection configuration
     *
     * @access private
     * @var object
     */
    protected static $_conn;
    /**
     * PDO Constant Attributes List
     *
     * @access private
     * @var \PDO Object
     */
    private static $_pdo_attributes = array(
        \PDO::ATTR_AUTOCOMMIT => 'AUTOCOMMIT',
        \PDO::ATTR_CASE => 'CASE',
        \PDO::ATTR_CLIENT_VERSION => 'CLIENT_VERSION',
        \PDO::ATTR_CONNECTION_STATUS => 'CONNECTION_STATUS',
        \PDO::ATTR_DRIVER_NAME => 'DRIVER_NAME',
        \PDO::ATTR_ERRMODE => 'ERRMODE',
        //\PDO::ATTR_ORACLE_NULLS => 'ORACLE_NULLS',
        \PDO::ATTR_PERSISTENT => 'PERSISTENT',
        //\PDO::ATTR_PREFETCH => 'PREFETCH',
        \PDO::ATTR_SERVER_INFO => 'SERVER_INFO',
        \PDO::ATTR_SERVER_VERSION => 'SERVER_VERSION',
        //\PDO::ATTR_TIMEOUT => 'TIMEOUT',
    );

    /**
     * PDO Compatible Drivers List
     *
     * @access private
     * @var array
     */
    private static $_pdo_drivers = array(
        'cubrid' => 'Cubrid',
        'mysql' => 'MySQL',
        'mssql' => 'MsSQL',
        'firebird' => 'Firebird',
        'ibm' => 'IBM',
        'informix' => 'Informix',
        'oracle' => 'Oracle',
        'odbc' => 'ODBC',
        'postgresql' => 'PostgreSQL',
        'sqlite' => 'SQLite',
        '4d' => '4D'
    );

    /**
     * Default PDO Connection Options
     *
     * @access private
     * @var array
     */
    private static $_pdo_options = array(
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_STRINGIFY_FETCHES => FALSE,
        \PDO::ATTR_EMULATE_PREPARES => FALSE,
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, FALSE,
        \PDO::ATTR_PERSISTENT => TRUE
    );

    /**
     * PDO Last Run Query Stream
     *
     * @access private
     * @var string
     */
    private static $_pdo_query = NULL;

    /**
     * Last Run Query
     *
     * @access private
     * @var string
     */
    private static $_last_query = NULL;

    /**
     * Run Queries
     *
     * @access private
     * @var string
     */
    private static $_queries = array();

    public static function connect($buffered = FALSE)
    {
        if(! in_array(strtolower(self::$_conn->driver), self::supported_drivers()))
        {
            $message = 'Unsupported database driver: ' . self::$_pdo_drivers[self::$_conn->driver];
        }

        $dsn = array(
            self::$_conn->driver . ':host=' . self::$_conn->host,
            'port=' . self::$_conn->port,
            'dbname=' . self::$_conn->database
        );

        if(! empty(self::$_conn->charset))
        {
            array_push($dsn, 'charset='.strtolower(self::$_conn->charset));
        }

        if(! empty(self::$_conn->collation))
        {
            array_push($dsn, 'collation='.strtolower(self::$_conn->collation));
        }

        self::$_conn->dsn = implode(';', $dsn);

        self::$_conn->options = self::$_pdo_options;

        if(self::$_conn->persistent === FALSE)
        {
            array_pop(self::$_conn->options);
        }

        try
        {
            self::$pdo = new \PDO(self::$_conn->dsn, self::$_conn->username, self::$_conn->password, self::$_conn->options);

            // Disable Mysql Buffered
            if($buffered === TRUE) self::$pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);

            // We can now log any exceptions on Fatal error.
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Disable emulation of prepared statements, use REAL prepared statements instead.
            self::$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);

            // Set Connection Flag
            self::$is_connected = TRUE;
        }
        catch (\PDOException $e)
        {
            $message = 'O2System is unable to connect to database: ' . $e->getMessage();
            log_message('error', $message);
            show_error($message);
        }
    }

    public static function supported_drivers()
    {
        return \PDO::getAvailableDrivers();
    }

    public static function create($table)
    {
        return self::$builder = new \O2ORM\Schema\Builder($table);
    }

    public static function store()
    {
        return self::$builder->store();
    }

    public static function conn_metadata()
    {
        $conn = new \O2ORM\Metadata();
        $conn->driver = self::$_pdo_drivers[self::$_conn->driver];
        $conn->port = self::$_conn->port;
        $conn->database = self::$_conn->database;
        $conn->dsn = self::$_conn->dsn;
        $conn->status = self::$pdo->getAttribute(7);
        $conn->persistent = self::$pdo->getAttribute(12);
        @$conn->server->version = self::$pdo->getAttribute(4);

        $server_info = explode('  ',self::$pdo->getAttribute(6));
        foreach($server_info as $info)
        {
            $x_info = explode(' ', $info);
            $info_name = strtolower(\O2ORM\Stringer::prepare(reset($x_info)));
            $info_data = end($x_info);

            if($info_name == 'queries')
            {
                $info_name = 'queries_per_second_avg';
            }

            @$conn->server->{$info_name} = $info_data;
        }

        $conn->client_version = self::$pdo->getAttribute(5);
        @$conn->pdo->auto_commit = self::$pdo->getAttribute(0);
        @$conn->pdo->error_mode = self::$pdo->getAttribute(3);
        @$conn->pdo->buffered = self::$pdo->getAttribute(12);

        return $conn;
    }

    /**
     * Database platform
     *
     * @access public
     * @return string
     */
    public static function platform()
    {
        $platform = new \O2ORM\Metadata();
        $platform->name = self::$_pdo_drivers[self::$_conn->driver];
        $platform->version = self::$pdo->getAttribute(4);

        return $platform;
    }

    /**
     * Database platform version
     *
     * @access public
     * @return string
     */
    public static function version()
    {
        return self::$pdo->getAttribute(4);
    }

    /**
     * Close database connection
     *
     * @access public
     * @return void
     */
    public function close()
    {
        self::$_pdo_query = NULL;
        self::$pdo = NULL;
        self::$is_connected = FALSE;
    }
}