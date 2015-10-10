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

namespace O2System\ORM\Interfaces;

// ------------------------------------------------------------------------
use O2System\ORM\Model;

/**
 * ORM Relation Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/factory
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORM
 */
abstract class Relation
{
    /**
     * Reference of active ORM model instance
     *
     * @access  protected
     *
     * @type    object  Instance of O2System\ORM model
     */
    protected $_model;

    /**
     * Indexing field of related table
     *
     * @access  protected
     *
     * @type    string
     */
    protected $_foreign_key = '';

    /**
     * Reference of related table
     *
     * @access  protected
     *
     * @type    string
     */
    protected $_reference_table;

    /**
     * Reference key of reference table
     *
     * @type string
     */
    protected $_reference_key = 'id';

    /**
     * Reference of related ORM model instance
     *
     * @access  protected
     *
     * @type    object  Instance of O2System\ORM model
     */
    protected $_reference_model;

    /**
     * Class Constructor
     *
     * @access  public
     * @final   this method can't be overwrite
     *
     * @uses    O2System\Core\Loader::helper()
     *
     * @property-write  $_model, $_db
     *
     * @param ORM       $model
     */
    public function __construct( Model $model )
    {
        // set reference of ORM model
        $this->_model =& $model;

        // reconstruct if exists
        if( method_exists( $this, '__reconstruct' ) )
        {
            $this->__reconstruct();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set Reference
     *
     * Set reference from table name, model name or model instance
     *
     * @access  public
     *
     * @property-read       $_related_model
     * @property-write      $_related_table
     *
     * @param string|object $reference table name, model name or instance of ORM model
     */
    public function set_reference( $reference )
    {
        // load related model
        $reference_model = $this->_load_reference_model( $reference );

        if( $reference_model instanceof Model )
        {
            $this->_reference_model =& $reference_model;
            $this->_reference_table = $reference_model->table;
        }
        else
        {
            if( strpos( $reference, '.' ) !== FALSE )
            {
                $x_reference = explode( '.', $reference );

                $this->_set_reference_table( $x_reference[ 0 ] );
                $this->_set_reference_key( $x_reference[ 1 ] );
            }
            else
            {
                $this->_set_reference_table( $reference );
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set Keys
     *
     * Set all needed fields name of working table and reference table
     *
     * @access  public
     * @final   this method can't be overwrite
     *
     * @param array $keys
     */
    final public function set_keys( array $keys )
    {
        foreach( $keys as $name => $field )
        {
            $name = '_' . $name;

            if( isset( $this->{$name} ) )
            {
                $this->{$name} = $field;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Load Reference Model
     *
     * @access  protected
     *
     * @property-write        $_related_model
     *
     * @param   string|object $related model name or instance of ORM model
     */
    protected function _load_reference_model( $reference )
    {
        if( $reference instanceof Model )
        {
            return $reference;
        }
        else
        {
            $class_name = prepare_namespace( $reference );

            if( class_exists( $class_name ) )
            {
                return new $class_name();
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set Related Table
     *
     * @access  protected
     *
     * @property-read   $_model
     * @property-write  $_related_table
     *
     * @param   string  $table
     */
    protected function _set_reference_table( $table )
    {
        foreach( $this->_model->table_prefixes as $prefix )
        {
            if( in_array( $reference_table = $prefix . $table, $this->_model->db->list_tables() ) )
            {
                $this->_reference_table = $reference_table;
                break;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set Related Table
     *
     * @access  protected
     *
     * @property-read   $_model
     * @property-write  $_related_table
     *
     * @param   string  $key
     */
    protected function _set_reference_key( $key )
    {
        if( ! empty( $this->_reference_table ) )
        {
            if( in_array( $key, $this->_model->db->list_fields( $this->_reference_table ) ) )
            {
                $this->_reference_key = $key;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set Foreign Key
     *
     * @access  public
     *
     * @property-read       $_related_table, $_model, $_foreign_key
     * @property-write      $_index_key
     *
     * @param   string|null $foreign_key   working table foreign key
     */
    public function set_foreign_key( $foreign_key = NULL )
    {
        if( isset( $foreign_key ) )
        {
            $this->_foreign_key = $foreign_key;
        }
        else
        {
            $x_table = explode( '_', $this->_reference_table );

            foreach( $x_table as $key )
            {
                if( ! in_array( $key . '_', $this->_model->table_prefixes ) )
                {
                    $clean_x_table[ ] = singular( $key );
                }
            }

            $foreign_keys = array(
                $this->_reference_key . '_' . implode( '_', $clean_x_table ),
                implode( '_', $clean_x_table ) . '_' . $this->_reference_key
            );

            foreach( $foreign_keys as $index_key )
            {
                if( in_array( $index_key, $this->_model->db->list_fields( $this->_model->table ) ) )
                {
                    $this->_foreign_key = $index_key;
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Result
     *
     * Abstract: extended class of Relation must implements result method
     *
     * @return mixed
     */
    abstract public function result();
}