<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4 or newer
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com
 * @since          Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\ORM;

// ------------------------------------------------------------------------

use O2System\DB;

class Model
{
    public $db;
    public $table;
    public $primary_key  = 'id';
    public $primary_keys = array( 'id' );

    public $table_prefixes = array(
        '', // none prefix
        'tm_', // table master prefix
        't_', // table data prefix
        'tr_', // table relation prefix
        'ts_', // table statistic prefix
        'tb_', // table buffer prefix
    );

    public $mapper;

    public function __construct()
    {
        // set mapper instance
        if( ! isset( $this->mapper ) )
        {
            $this->mapper = new Factory\Mapper( $this );
        }
    }

    public function load_database( $connection = NULL )
    {
        if( ! isset( $this->db ) AND isset( $connection ) )
        {
            $DB = new DB();
            $this->db = $DB->connect( $connection );
        }
    }

    /**
     * All
     *
     * Get all rows of table
     *
     * @param   array $conditions Where clause conditions
     *
     * @access  public
     * @return  array
     */
    public function all( $conditions = array() )
    {
        $fields = $this->db->list_fields( $this->table );

        // Sort by record left
        if( in_array( 'record_left', $fields ) )
        {
            $this->db->order_by( 'record_left', 'ASC' );
        }

        // Sort by record ordering
        if( in_array( 'record_ordering', $fields ) )
        {
            $this->db->order_by( 'record_ordering', 'ASC' );
        }

        if( ! empty( $conditions ) )
        {
            $this->db->where( $conditions );
        }

        $query = $this->db->get( $this->table );

        if( $query->num_rows() > 0 )
        {
            return $query->result( '\O2System\ORM\Factory\Result', $this );
        }

        return array();
    }

    /**
     * Rows
     *
     * Alias for All Method
     *
     * @param   array $conditions Where clause conditions
     *
     * @access  public
     * @return  array
     */
    public function rows( $conditions = array() )
    {
        return $this->all( $conditions );
    }
    // ------------------------------------------------------------------------

    /**
     * Find
     *
     * Find single record base on criteria by specific field
     *
     * @param   string      $criteria Criteria value
     * @param   string|null $field    Table column field name | set to primary key by default
     *
     * @access  public
     * @return  null|object  O2System\ORM\Factory\Result
     */
    public function find( $criteria, $field = NULL )
    {
        $field = isset( $field ) ? $field : $this->primary_key;

        // build relations mapper
        $this->mapper->build();

        $query = $this->db->limit( 1 )->where( $field, $criteria )->get( $this->table );

        if( $query->num_rows() > 0 )
        {
            $result = new Factory\Result( $this );

            return $query->first_row( $result );
        }

        return NULL;
    }
    // ------------------------------------------------------------------------

    /**
     * Find By
     *
     * Find single record based on certain conditions
     *
     * @param   array $conditions List of conditions with criteria
     *
     * @access  public
     * @return  null|object O2System\ORM\Factory\Result
     */
    public function find_by( array $conditions )
    {
        // build relations mapper
        $this->mapper->build();

        $query = $this->db->limit( 1 )->where( $conditions )->get( $this->table );

        if( $query->num_rows() > 0 )
        {
            $result = new Factory\Result( $this );

            return $query->first_row( $result );
        }

        return NULL;
    }
    // ------------------------------------------------------------------------

    /**
     * Find In
     *
     * Find many records within criteria on specific field
     *
     * @param   array  $in_criteria List of criteria
     * @param   string $field       Table column field name | set to primary key by default
     *
     * @access  public
     * @return  array
     */
    public function find_in( array $in_criteria, $field = 'id' )
    {
        $field = isset( $field ) ? $field : $this->primary_key;

        // build relations mapper
        $this->mapper->build();

        $query = $this->db->where_in( $field, $in_criteria )->get( $this->table );

        if( $query->num_rows() > 0 )
        {
            return $query->result( '\O2System\ORM\Factory\Result', $this );
        }

        return array();
    }
    // ------------------------------------------------------------------------

    /**
     * Find In
     *
     * Find many records not within criteria on specific field
     *
     * @param   array  $not_in_criteria List of criteria
     * @param   string $field           Table column field name | set to primary key by default
     *
     * @access  public
     * @return  array
     */
    public function find_not_in( array $not_in_criteria, $field = 'id' )
    {
        $field = isset( $field ) ? $field : $this->primary_key;

        // build relations mapper
        $this->mapper->build();

        $query = $this->db->where_in( $field, $not_in_criteria )->get( $this->table );

        if( $query->num_rows() > 0 )
        {
            return $query->result( '\O2System\ORM\Factory\Result', $this );
        }

        return array();
    }
    // ------------------------------------------------------------------------

    /**
     * Find Many
     *
     * Find many records within criteria on specific field
     *
     * @param   array  $criteria Criteria value
     * @param   string $field    Table column field name | set to primary key by default
     *
     * @access  public
     * @return  array
     */
    public function find_many( $criteria, $field = NULL )
    {
        $field = isset( $field ) ? $field : $this->primary_key;

        // build relations mapper
        $this->mapper->build();

        $query = $this->db->where( $field, $criteria )->get( $this->table );

        if( $query->num_rows() > 0 )
        {
            return $query->result( '\O2System\ORM\Factory\Result', $this );
        }

        return array();
    }
    // ------------------------------------------------------------------------

    /**
     * Find Many By
     *
     * Find many records based on certain conditions
     *
     * @access  public
     *
     * @param array $conditions list of conditions with criteria
     *
     * @return null|object  O2System\ORM\Factory\Result
     */
    public function find_many_by( array $conditions )
    {
        // build relations mapper
        $this->mapper->build();

        foreach( $conditions as $field => $in_criteria )
        {
            if( is_string( $in_criteria ) )
            {
                $this->db->where( $field, $in_criteria );
            }
            elseif( is_array( $in_criteria ) )
            {
                $this->db->where_in( $field, $in_criteria );
            }
        }

        $query = $this->db->get( $this->table );

        if( $query->num_rows() > 0 )
        {
            return $query->result( '\O2System\ORM\Factory\Result', $this );
        }

        return array();
    }
    // ------------------------------------------------------------------------

    /**
     * Row
     *
     * Get single row of model query result
     *
     * @access  public
     *
     * @uses    O2System\ORM\Factory\Query()
     *
     * @return null|object  O2System\ORM\Factory\Result
     */
    public function row()
    {
        if( isset( $this->row ) )
        {
            return $this->row;
        }
        else
        {
            // build relation mapper
            $this->mapper->build();

            $query = $this->db->from( $this->table )->get();

            if( $query->num_rows() > 0 )
            {
                $result = new Factory\Result( $this );

                return $query->first_row( $result );
            }
        }

        return NULL;
    }
    // ------------------------------------------------------------------------


    /**
     * Belongs To
     *
     * Define an inverse one-to-one or many relationship.
     *
     * @access  public
     * @final   this method can't be overwrite
     *
     * @uses    O2System\ORM\Relations\Belongs_to()
     *
     * @param string $reference   table name, model name or instance of ORM model
     * @param null   $foreign_key working table foreign key
     *
     * @return mixed
     */
    final public function belongs_to( $reference, $foreign_key = NULL )
    {
        $belongs_to = new Relations\Belongs_To( $this );

        $belongs_to->set_reference( $reference );

        if( isset( $foreign_key ) )
        {
            $belongs_to->set_foreign_key( $foreign_key );
        }

        return $belongs_to->result();
    }
    // ------------------------------------------------------------------------

    /**
     * Define a many-to-many relationship.
     *
     * @param  string $related
     * @param  string $table
     * @param  string $foreign_key
     * @param  string $other_key
     * @param  string $relation
     *
     * @return array
     */
    public function belongs_to_many( $related, $table = NULL, $foreign_key = NULL, $other_key = NULL, $relation = NULL )
    {
        $belongs_to_many = new Relations\Belongs_To_Many( $this );
    }
    // ------------------------------------------------------------------------

    /**
     * Has One
     *
     * Define a one-to-one relationship.
     *
     * @access  public
     * @final   this method can't be overwrite
     *
     * @uses    O2System\ORM\Relations\Has_one()
     *
     * @param string $reference   table name, model name or instance of ORM model
     * @param null   $foreign_key working table foreign key
     *
     * @return mixed
     */
    final public function has_one( $reference, $foreign_key = NULL )
    {
        $has_many = new Relations\Has_one( $this );

        if( strpos( $reference, '.' ) !== FALSE )
        {
            $x_reference = explode( '.', $reference );

            $has_many->set_reference( $x_reference[ 0 ] );
            $has_many->set_reference_key( $x_reference[ 1 ] );
        }
        else
        {
            $has_many->set_reference( $reference );
        }

        if( isset( $foreign_key ) )
        {
            $has_many->set_foreign_key( $foreign_key );
        }

        return $has_many->result();
    }
    // ------------------------------------------------------------------------

    /**
     * Has Many
     *
     * Define a one-to-many relationship.
     *
     * @access  public
     * @final   this method can't be overwrite
     *
     * @uses    O2System\ORM\Relations\Has_one()
     *
     * @param string $reference   table name, model name or instance of ORM model
     * @param null   $foreign_key working table foreign key
     *
     * @return mixed
     */
    public function has_many( $reference, $foreign_key = NULL )
    {
        $has_many = new Relations\Has_Many( $this );

        $has_many->set_reference( $reference );

        if( isset( $foreign_key ) )
        {
            $has_many->set_foreign_key( $foreign_key );
        }

        return $has_many->result();
    }
    // ------------------------------------------------------------------------

    /**
     * Define a has-many-through relationship.
     *
     * @param  string      $related
     * @param  string      $through
     * @param  string|null $first_key
     * @param  string|null $second_key
     *
     */
    public function has_many_through( $related, $through, $first_key = NULL, $second_key = NULL )
    {
        $has_many_through = new Relations\Has_many_through( $this );
    }
    // ------------------------------------------------------------------------

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param  string      $related
     * @param  string      $name
     * @param  string|null $type
     * @param  string|null $id
     * @param  string|null $local_key
     *
     */
    public function morph_many( $related, $name, $type = NULL, $id = NULL, $local_key = NULL )
    {
        $morph_many = new Relations\Morph_many( $this );
    }
    // ------------------------------------------------------------------------

    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @param  string $related
     * @param  string $name
     * @param  string $table
     * @param  string $foreign_key
     * @param  string $other_key
     * @param  bool   $inverse
     *
     */
    public function morph_to_many( $related, $name, $table = NULL, $foreign_key = NULL, $other_key = NULL, $inverse = FALSE )
    {
        $morph_to_many = new Relations\Morph_to_many( $this );
    }
    // ------------------------------------------------------------------------

    /**
     * Set the relationships that should be eager loaded.
     *
     * @access  public
     *
     * @uses    O2System\ORM\Relations\With()
     *
     * @return $this
     */
    public function with()
    {
        $with = new Relations\With( $this );
        $with->set_references( func_get_args() );

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Has
     *
     * Set relationship based on table name, model name, or instance of ORM model
     *
     * @access  public
     *
     * @uses    O2System\ORM\Relations\With()
     *
     * @return $this
     */
    public function has()
    {
        $has = new Relations\Has( $this );
        $has->set_references( func_get_args() );

        return $this;
    }

    /**
     * @param array $row
     */
    public function insert(array $row)
    {

    }

    /**
     * Insert multiple rows into the table. Returns an array of multiple IDs.
     *
     * @param array $row
     *
     * @return mixed
     */
    public function insert_many( array $row )
    {
        // TODO: Implement insert_many() method.
    }
    // ------------------------------------------------------------------------

    public function update($row)
    {

    }

    public function update_by($row, $conditions)
    {

    }

    /**
     * Updated a record based on sets of ids.
     *
     * @param array $ids
     * @param array $data
     *
     * @return mixed
     */
    public function update_many( array $ids, array $data = array() )
    {
        // TODO: Implement update_many() method.
    }
    // ------------------------------------------------------------------------

    public function trash($id)
    {

    }

    public function trash_by($id, $conditions = array())
    {

    }

    /**
     * Trash many rows from the database table based on sets of ids.
     *
     * @param array $ids
     *
     * @return mixed
     */
    public function trash_many( array $ids )
    {
        // TODO: Implement trash_many() method.
    }
    // ------------------------------------------------------------------------

    public function trash_many_by(array $ids, $conditions = array())
    {

    }

    public function delete($id)
    {

    }

    public function delete_by($id, $conditions = array())
    {

    }

    /**
     * Delete many rows from the database table based on sets of ids.
     *
     * @param array $ids
     *
     * @return mixed
     */
    public function delete_many( array $ids )
    {
        // TODO: Implement delete_many() method.
    }

    public function delete_many_by(array $ids, $conditions = array())
    {

    }
}

use O2System\Gears\Tracer;

class Exception extends \Exception
{
    /**
     * Class Constructor
     *
     * @param null       $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct( $message = NULL, $code = 0, \Exception $previous = NULL )
    {
        parent::__construct( $message, $code, $previous );
        set_exception_handler( '\O2System\ORM\Exception::exception_handler' );
    }

    // ------------------------------------------------------------------------

    /**
     * Exception Handler
     *
     * @param $exception
     */
    public static function exception_handler( $exception )
    {
        $tracer = new Tracer( (array)$exception->getTrace() );

        if( PHP_SAPI === 'cli' )
        {
            $template = __DIR__ . '/Views/cli_exception.php';
        }
        else
        {
            $template = __DIR__ . '/Views/html_exception.php';
        }

        if( ob_get_level() > 1 )
        {
            ob_end_flush();
        }

        header( 'HTTP/1.1 500 Internal Server Error', TRUE, 500 );

        ob_start();
        include( $template );
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;

        exit( 1 );
    }
}

