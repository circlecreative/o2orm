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
defined( 'BASEPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\ORM;
use O2System\ORM\Factory\Relation;
use O2System\ORM\Factory\Query;

/**
 * ORM Has One Relationship Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/factory
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORMHasOne
 */
class Has_one extends Relation
{
    /**
     * Class Constructor
     *
     * @access  public
     *
     * @property-write  $_reference_key
     */
    public function __reconstruct()
    {
        $this->_reference_key = NULL;
    }
    // ------------------------------------------------------------------------

    /**
     * Set Reference Key
     *
     * @access  public
     *
     * @param string|null   $reference_key
     */
    public function set_reference_key( $reference_key = NULL )
    {
        if( isset( $reference_key ) )
        {
            $this->_reference_key = $reference_key;
        }
        else
        {
            $reference_keys = array(
                $this->_model->primary_key . '_' . $this->_model->alias,
                $this->_model->alias . '_' . $this->_model->primary_key
            );

            if( $this->_reference_model instanceof ORM )
            {
                $reference_fields = $this->_reference_model->fields;
            }
            else
            {
                $query = $this->_db->limit( 1 )->get( $this->_reference_table );
                $reference_fields = $query->list_fields();
            }

            foreach( $reference_keys as $field )
            {
                if( $this->_reference_model instanceof ORM )
                {
                    if( in_array( $field, $reference_fields ) )
                    {
                        $this->_reference_key = $field;
                        break;
                    }
                }
            }
        }
    }
    // ------------------------------------------------------------------------

    /**
     * Result
     *
     * Belongs to query result
     *
     * @access  public
     *
     * @uses    O2System\ORM\Factory\Query
     *
     * @return  mixed
     */
    public function result()
    {
        if( ! isset( $this->_reference_key ) )
        {
            $this->set_reference_key();
        }

        $query = $this->_model->db->limit( 1 )
                                  ->where( $this->_reference_key, $this->_model->row()->{$this->_model->primary_key} )
                                  ->get( $this->_reference_model->table );

        if( $this->_reference_model instanceof ORM )
        {
            $row = new Query( $query, $this->_reference_model );
        }
        else
        {
            $row = new Query( $query, $this->_model );
        }

        return $row->result();
    }
}