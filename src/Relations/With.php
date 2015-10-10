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

namespace O2System\ORM\Relations;

// ------------------------------------------------------------------------

use O2System\ORM\Interfaces\Relation;
use O2System\ORM\Model;

/**
 * ORM With Relation Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/relations
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORMWith
 */
class With extends Relation
{
    /**
     * Set Relations
     *
     * @access  public
     *
     * @param   array $references list of references
     */
    public function set_references( array $references )
    {
        foreach( $references as $reference )
        {
            if( $reference_model = $this->_load_reference_model( $reference ) )
            {
                $this->_set_reference_model( $reference_model );
            }
            elseif( strpos( $reference, '.' ) !== FALSE )
            {
                $x_reference = explode( '.', $reference );
                list( $reference, $reference_key ) = $x_reference;

                $this->_set_reference_table( $reference, $reference_key );
            }
            else
            {
                $this->_set_reference_table( $reference );
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set Related Table
     *
     * Set object relation mapper from table name
     *
     * @access  protected
     *
     * @param string $reference     reference table name
     * @param string $reference_key reference table index field
     */
    protected function _set_reference_table( $reference, $reference_key = 'id' )
    {
        $x_reference = explode( '_', $reference );
        $x_reference = array_map( 'singular', $x_reference );
        $reference_alias = implode( '_', $x_reference );

        $foreign_keys = array(
            $reference_key . '_' . $reference_alias,
            $reference_alias . '_' . $reference_key
        );

        foreach( $foreign_keys as $field )
        {
            if( in_array( $field, $this->_model->db->list_fields( $this->_model->table ) ) )
            {
                $foreign_key = $field;
                break;
            }
        }

        foreach( $this->_model->table_prefixes as $prefix )
        {
            if( in_array( $reference_table = $prefix . $reference, $this->_model->db->list_tables() ) )
            {
                $reference_fields = $this->_model->db->list_fields( $reference_table );

                $this->_model->mapper->add( $foreign_key, $reference_alias, $reference_table, $reference_key, $reference_fields );
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Set Related Model
     *
     * Set object relation mapper from model
     *
     * @access  protected
     *
     * @param   object $reference instance of O2System\ORM model
     */
    protected function _set_reference_model( $reference )
    {
        $x_reference = explode( '_', $reference->table );

        foreach( $x_reference as $key )
        {
            if( ! in_array( $key . '_', $this->_model->table_prefixes ) )
            {
                $clean_x_table[ ] = singular( $key );
            }
        }

        $reference_alias = implode( '_', $clean_x_table );

        if( isset( $reference->primary_key ) )
        {
            $foreign_keys = array(
                $reference->primary_key . '_' . $reference_alias,
                $reference_alias . '_' . $reference->primary_key
            );
        }
        elseif( isset( $reference->primary_keys ) )
        {
            foreach( $reference->primary_keys as $primary_key )
            {
                $foreign_keys = array(
                    $primary_key . '_' . $reference_alias,
                    $reference_alias . '_' . $primary_key
                );
            }
        }

        if( isset( $foreign_keys ) )
        {
            $reference->fields = $this->_model->db->list_fields( $reference->table );

            foreach( $foreign_keys as $foreign_key )
            {
                if( in_array( $foreign_key, $reference->fields ) )
                {
                    $this->_model->mapper->add( $foreign_key, $reference_alias, $reference->table, $reference->primary_key, $reference->fields );
                    break;
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Result
     *
     * Only for implements Relation::result() method
     *
     * @return NULL
     */
    public function result()
    {
        return NULL;
    }
}