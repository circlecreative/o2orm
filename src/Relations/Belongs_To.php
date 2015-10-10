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

use O2System\ORM\Factory\Result;
use O2System\ORM\Interfaces\Relation;
use O2System\ORM\Model;

/**
 * ORM Belongs To Relationship Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/factory
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORMBelongsTo
 */
class Belongs_To extends Relation
{
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
        if( $this->_reference_model instanceof Model )
        {
            if(method_exists($this->_reference_model, 'get_row'))
            {
                return $this->_reference_model->get_row();
            }
            elseif( ! empty( $this->_reference_model->primary_key ) )
            {
                if( $this->_foreign_key == '' )
                {
                    $this->set_foreign_key();
                }

                return $this->_reference_model->find( $this->_model->row()->{$this->_foreign_key}, $this->_reference_model->primary_key );
            }
            elseif( ! empty( $this->_reference_model->primary_keys ) )
            {
                foreach( $this->_reference_model->primary_keys as $key => $primary_key )
                {
                    $conditions[ $primary_key ] = $this->_model->row()->{$this->_foreign_keys[ $key ]};
                }

                return $this->_reference_model->find_by( $conditions );
            }
        }
        elseif( ! empty( $this->_model->table ) )
        {
            if(method_exists($this->_model, 'get_row'))
            {
                return $this->_model->get_row();
            }
            else
            {
                if( $this->_foreign_key == '' )
                {
                    $this->set_foreign_key();
                }

                return $this->_model->find_by( [ $this->_reference_key => $this->_model->row()->{$this->_foreign_key} ] );
            }
        }
        elseif( ! empty( $this->_reference_table ) )
        {
            if( $this->_foreign_key == '' )
            {
                $this->set_foreign_key();
            }

            $query = $this->_model->db->limit( 1 )
                                      ->where( $this->_reference_key, $this->_model->row()->{$this->_foreign_key} )
                                      ->get( $this->_reference_table );

            if( $query->num_rows() > 0 )
            {
                return $query->first_row( new Result( $this->_model ) );
            }
        }
    }
}