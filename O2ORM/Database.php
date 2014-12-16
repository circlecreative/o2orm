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
 * Database Class
 *
 * @package     O2ORM
 * @subpackage
 * @category    Core Class
 * @author      Steeven Andrian Salim
 * @link        http://circle-creative.com/products/o2orm/user-guide/core/database.html
 */
// ------------------------------------------------------------------------

class Database
{
    /**
     * Active connection configuration
     *
     * @access private
     * @var object
     */
    private $_conn;

    /**
     * PDO connection stream
     *
     * @access public
     * @var \PDO Object
     */
    private $_pdo = NULL;

    /**
     * PDO Constant Attributes List
     *
     * @access private
     * @var \PDO Object
     */
    private $_pdo_attributes = array(
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
    private $_pdo_drivers = array(
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
    private $_pdo_options = array(
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_STRINGIFY_FETCHES => FALSE,
        \PDO::ATTR_EMULATE_PREPARES => FALSE,
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, FALSE,
        \PDO::ATTR_PERSISTENT => TRUE
    );

    /**
     * Last Run Query
     *
     * @access private
     * @var string
     */
    private $_last_query = NULL;

    /**
     * Run Queries
     *
     * @access private
     * @var string
     */
    private $_queries = array();

    /**
     * Active Queries
     *
     * @access private
     * @var string
     */
    private $_active_query = array();

    /**
     * Class constructor
     *
     * @access public
     * @return void
     */
    public function __construct(array $config = array(), $buffered = FALSE)
    {
        if(! empty($config))
        {
            $this->_conn = (object) $config;
            $this->__pdo_connect($buffered);
        }

        if($this->is_connected)
        {
            $driver_name = $this->_pdo_drivers[$this->_conn->driver];
            $query_driver = '\O2ORM\Drivers\\' . $driver_name . '\Query';
            $table_driver = '\O2ORM\Drivers\\' . $driver_name . '\Table';

            // Query Driver
            $this->query = new $query_driver();
            $this->table = new $table_driver();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Server supported drivers
     *
     * @access public
     * @return array
     */
    public function supported_drivers()
    {
        return \PDO::getAvailableDrivers();
    }

    // ------------------------------------------------------------------------

    /**
     * Connect to database using PDO
     *
     * @access private
     * @return void
     */
    private function __pdo_connect($buffered = FALSE)
    {
        if(! in_array(strtolower($this->_conn->driver), $this->supported_drivers()))
        {
            $message = 'Unsupported database driver: ' . $this->_pdo_drivers[$this->_conn->driver];
            log_message('error', $message);
            show_error($message);
        }

        $dsn = array(
            $this->_conn->driver . ':host=' . $this->_conn->host,
            'port=' . $this->_conn->port,
            'dbname=' . $this->_conn->database
        );

        if(! empty($this->_conn->charset))
        {
            array_push($dsn, 'charset='.strtolower($this->_conn->charset));
        }

        if(! empty($this->_conn->collation))
        {
            array_push($dsn, 'collation='.strtolower($this->_conn->collation));
        }

        $this->_conn->dsn = implode(';', $dsn);

        $this->_conn->options = $this->_pdo_options;

        if($this->_conn->persistent === FALSE)
        {
            array_pop($this->_conn->options);
        }

        try
        {
            $this->_pdo = new \PDO($this->_conn->dsn, $this->_conn->username, $this->_conn->password, $this->_conn->options);

            // Disable Mysql Buffered
            if($buffered === TRUE) $this->_pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);

            // We can now log any exceptions on Fatal error.
            $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->_pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, TRUE);

            // Set Connection Flag
            $this->is_connected = TRUE;
        }
        catch (\PDOException $e)
        {
            $message = 'O2ORM is unable to connect to database: ' . $e->getMessage();
            log_message('error', $message);
            show_error($message);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Connect to database using PDO
     *
     * @access private
     * @return void
     */
    public function reconnect($buffered = FALSE)
    {
        $this->close();
        $this->__pdo_connect($buffered);
    }

    /**
     * Database connection id
     *
     * @access public
     * @return SQL Conection ID
     */
    public function conn_id()
    {
        $conn_id = $this->_pdo->query('SELECT CONNECTION_ID()')->fetch(\PDO::FETCH_ASSOC);
        @$this->_conn->id = $conn_id['CONNECTION_ID()'];
        return $this->_conn->id;
    }

    // ------------------------------------------------------------------------

    /**
     * Execute query using PDO
     *
     * @access public
     * @return void
     */
    public function execute($sql)
    {
        // Execute query
        $this->_pdo->exec($sql);

        // Set last query
        $this->_last_query = $sql;

        // Collects queries
        $this->_queries[] = $sql;
    }

    // ------------------------------------------------------------------------

    /**
     * Execute query using PDO
     *
     * @access public
     * @return void
     */
    public function query($sql)
    {
        // Set last query
        $this->_last_query = $sql;

        // Collects queries
        $this->_queries[] = $sql;

        // Execute query
        return $this->_pdo->query($sql);
    }

    // ------------------------------------------------------------------------

    /**
     * Get all rows query result
     *
     * @access public
     * @return object|array  \O2ORM\Schema\Results
     */
    public function result($return = 'object')
    {
        $sql = $this->query->sql();

        $query = $this->_pdo->prepare($sql);

        // Binds a parameter to the specified variable name
        $bindParams = $this->query->get_params();

        if(!empty($bindParams))
        {
            foreach($bindParams as $param)
            {
                if(isset($param->type))
                {
                    if(isset($param->length))
                    {
                        $query->bindParam($param->name, $param->value, $param->type, $param->length);
                    }
                    else
                    {
                        $query->bindParam($param->name, $param->value, $param->type);
                    }
                }
                else
                {
                    $query->bindParam($param->name, $param->value);
                }
            }
        }

        $query->execute();

        $this->_last_query = $sql;
        $this->_queries[] = $sql;

        if ($return === 'object')
        {
            \O2ORM\Schema\Results::as_object();
        }
        elseif ($return === 'array')
        {
            \O2ORM\Schema\Results::as_array();
        }

        $query->setFetchMode(\PDO::FETCH_CLASS, 'O2ORM\Schema\Results');
        $this->_active_query = $query;

        return $query->fetchAll();
    }

    // ------------------------------------------------------------------------

    /**
     * Get all rows query result and free the result
     *
     * @access public
     * @return object|array  \O2ORM\Schema\Results
     */
    public function free_result($return = 'object')
    {
        $results = $this->result($return);
        $this->_active_query = NULL;
        $this->_pdo->closeCursor();

        return $results;
    }

    // ------------------------------------------------------------------------

    /**
     * Get first row of query result
     *
     * @access public
     * @return object|array  \O2ORM\Schema\Results
     */
    public function row($return = 'object')
    {
        $sql = $this->query->sql();

        $query = $this->_pdo->prepare($sql);

        // Binds a parameter to the specified variable name
        $bindParams = $this->query->get_params();

        if(!empty($bindParams))
        {
            foreach($bindParams as $param)
            {
                if(isset($param->type))
                {
                    if(isset($param->length))
                    {
                        $query->bindParam($param->name, $param->value, $param->type, $param->length);
                    }
                    else
                    {
                        $query->bindParam($param->name, $param->value, $param->type);
                    }
                }
                else
                {
                    $query->bindParam($param->name, $param->value);
                }
            }
        }

        $query->execute();

        $this->_last_query = $sql;
        $this->_queries[] = $sql;

        if ($return === 'object')
        {
            \O2ORM\Schema\Results::as_object();
        }
        elseif ($return === 'array')
        {
            \O2ORM\Schema\Results::as_array();
        }

        $query->setFetchMode(\PDO::FETCH_CLASS, 'O2ORM\Schema\Results');

        $this->_active_query = $query;

        return $query->fetch();
    }

    // ------------------------------------------------------------------------

    /**
     * Get next set of query result row
     *
     * @access public
     * @return int num rows
     */
    public function next_row()
    {
        if(! empty($this->_active_query))
        {
            return $this->_active_query->nextRowset();
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get amount rows of query result
     *
     * @access public
     * @return int num rows
     */
    public function num_rows()
    {
        return $this->_active_query->rowCount();
    }

    // ------------------------------------------------------------------------

    /**
     * Rowsets
     *
     * @access public
     * @return \PDOStatement
     */
    public function rowsets()
    {
        $sql = 'CALL multiple_rowsets()';
        if($this->is_connected)
        {
            return $this->_pdo->query($sql);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Number of affected rows
     *
     * @access public
     * @return int
     */
    public function affected_rows()
    {
        return $this->_active_query->rowCount();
    }

    // ------------------------------------------------------------------------

    /**
     * Get last insert id
     *
     * @access public
     * @return int num rows
     */
    public function last_insert_id()
    {
        return $this->_pdo->lastInsertId();
    }

    // ------------------------------------------------------------------------

    /**
     * Get last query
     *
     * @access public
     * @return string
     */
    public function last_query()
    {
        return $this->_last_query;
    }

    // ------------------------------------------------------------------------

    /**
     * Get all run queries
     *
     * @access public
     * @return string
     */
    public function queries()
    {
        return $this->_queries;
    }

    // ------------------------------------------------------------------------

    /**
     * Call database function
     *
     * @access public
     * @return string
     */
    public function call_function()
    {

    }

    // ------------------------------------------------------------------------

    /**
     * Prepare string
     *
     * @access public
     * @return string
     */
    public function prepare_string($string)
    {
        $string = trim($string);
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        //Convert dash to underscore
        $string = str_replace('-', '_', $string);
        return $string;
    }

    // ------------------------------------------------------------------------

    public function conn_metadata()
    {
        $conn = new \O2ORM\Schema\Metadata\Objects();
        $conn->driver = $this->_pdo_drivers[$this->_conn->driver];
        $conn->port = $this->_conn->port;
        $conn->database = $this->_conn->database;
        $conn->dsn = $this->_conn->dsn;
        $conn->status = $this->_pdo->getAttribute(7);
        $conn->persistent = $this->_pdo->getAttribute(12);
        @$conn->server->version = $this->_pdo->getAttribute(4);

        $server_info = explode('  ',$this->_pdo->getAttribute(6));
        foreach($server_info as $info)
        {
            $x_info = explode(' ', $info);
            $info_name = strtolower($this->prepare_string(reset($x_info)));
            $info_data = end($x_info);

            if($info_name == 'queries')
            {
                $info_name = 'queries_per_second_avg';
            }

            @$conn->server->{$info_name} = $info_data;
        }

        $conn->client_version = $this->_pdo->getAttribute(5);
        @$conn->pdo->auto_commit = $this->_pdo->getAttribute(0);
        @$conn->pdo->error_mode = $this->_pdo->getAttribute(3);
        @$conn->pdo->buffered = $this->_pdo->getAttribute(12);

        return $conn;
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
        $this->_pdo = NULL;
        $this->is_connected = FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Database platform
     *
     * @access public
     * @return string
     */
    public function platform()
    {
        $platform = new \O2ORM\Schema\Metadata\Objects();
        $platform->name = $this->_pdo_drivers[$this->_conn->driver];
        $platform->version = $this->_pdo->getAttribute(4);

        return $platform;
    }

    // ------------------------------------------------------------------------

    /**
     * Database platform version
     *
     * @access public
     * @return string
     */
    public function version()
    {
        return $this->_pdo->getAttribute(4);
    }

    // ------------------------------------------------------------------------

    /**
     * Insert data
     *
     * @access public
     * @return void
     */
    public function insert($table, $data)
    {
        $this->query->insert($table, $data);

        // Get SQL
        $sql = $this->query->sql();

        $query = $this->_pdo->prepare($sql);

        // Binds a parameter to the specified variable name
        $bindParams = $this->query->get_params();

        foreach($bindParams as $param)
        {
            if(is_numeric($param->value))
            {
                $query->bindValue($param->name, $param->value, \PDO::PARAM_INT);
            }
            elseif(is_string($param->value))
            {
                try
                {
                    $query->bindValue($param->name, utf8_encode($param->value), \PDO::PARAM_STR);
                }
                catch(\PDOException $e)
                {

                }
            }
            elseif(is_bool($param->value))
            {
                $query->bindValue($param->name, $param->value, \PDO::PARAM_BOOL);
            }
            elseif(is_null($param->value))
            {
                $query->bindValue($param->name, $param->value, \PDO::PARAM_NULL);
            }

        }

        $query->execute();

        // Set last query
        $this->_last_query = $sql;

        // Collects queries
        $this->_queries[] = $sql;

        return $this->last_insert_id();
    }

    // ------------------------------------------------------------------------

    /**
     * Update data
     *
     * @access public
     * @return void
     */
    public function update($table, $data, $conditions)
    {
        $this->query->update($table, $data, $conditions);

        // Get SQL
        $sql = $this->query->sql();
        $this->execute($sql);

        return $this->last_insert_id();
    }

    // ------------------------------------------------------------------------

    /**
     * Delete data
     *
     * @access public
     * @return void
     */
    public function delete($table, $conditions)
    {
        $this->query->delete($table, $conditions);

        // Get SQL
        $sql = $this->query->sql();
        $this->execute($sql);

        return $this->last_insert_id();
    }

    // ------------------------------------------------------------------------

    /**
     * Create database
     *
     * @access public
     * @return void
     */
    public function create()
    {

    }

    // ------------------------------------------------------------------------

    /**
     * Drop database
     *
     * @access public
     * @return void
     */
    public function drop()
    {

    }

    // ------------------------------------------------------------------------

    /**
     * Optimize database
     *
     * @access public
     * @return void
     */
    public function optimize()
    {

    }
}

/* End of file Database.php */
/* Location: ./O2ORM/Database.php */