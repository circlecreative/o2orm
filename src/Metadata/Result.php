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

namespace O2System\ORM\Metadata;

// ------------------------------------------------------------------------

use O2System\ORM\Model;

/**
 * ORM Result Factory Class
 *
 * @package         O2System
 * @subpackage      core/orm/factory
 * @category        core libraries driver factory
 * @author          Circle Creative Dev Team
 * @link            http://o2system.center/wiki/#ORM
 */
class Result
{
    /**
     * Reference of active ORM model instance
     *
     * @access  protected
     * @static  static class property
     *
     * @type    Model
     */
    protected $_model = NULL;

    protected $_references = array();

    /**
     * Class Constructor
     *
     * @access  public
     * @final   this class can't be overwrite
     *
     * @property-write  static::$_model
     *
     * @param   Model        $model  reference of active ORM model instance
     */
    public function __construct(  )
    {


        if (! empty( $this->_model->relations ) )
        {
            foreach ( $this->_model->relations as $relation )
            {
                if (! empty( $relation->references ) )
                {
                    foreach ( $relation->references as $reference )
                    {
                        $this->_references[] = $reference->object_key;
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    public function __set( $key, $value )
    {
        if(! empty( $this->_references ) )
        {
            foreach( $this->_references as $object_key )
            {
                if( strpos( $key, $object_key ) !== FALSE )
                {
                    if( empty( $this->{$object_key} ) )
                    {
                        $model = clone $this->_model;
                        unset($model->relations);

                        $this->{$object_key} = new Result( $model );
                    }
                    else
                    {
                        $this->{$object_key}->{ trim( str_replace( $object_key, '', $key ), '_' ) } = $value;
                        return;
                    }
                }
            }
        }

        if( strpos($key, 'record_') !== FALSE )
        {
            if(empty($this->record))
            {
                $this->record = new Record( $this->_model );
            }

            $this->record->{ trim(str_replace('record', '', $key), '_') } = $value;
            return;
        }
        elseif( ! isset( $this->{$key} ) )
        {
            $this->{$key} = $value;
        }
    }

    /**
     * Get Override
     *
     * This method is act as magic method, inspired from Laravel Eloquent ORM
     *
     * @access  public
     *
     * @property-read   static::$_model
     *
     * @param   string $key property name
     *
     * @return  mixed
     */
    public function __get( $field )
    {
        if( property_exists( $this, $field ) )
        {
            return $this->{$field};
        }
        else
        {
            foreach( get_object_vars( $this ) as $key => $value )
            {
                $this->_model->__set( $key, $value );
            }

            if( is_callable( array( $this->_model, $field ) ) )
            {
                return call_user_func( array( $this->_model, $field ), get_object_vars($this) );
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Call Override
     *
     * This method is act as magic method, inspired from Laravel Eloquent ORM
     *
     * @access  public
     *
     * @param   string $method
     * @param   array  $args
     *
     * @return  mixed
     */
    public function __call( $method, $args = array() )
    {
        return $this->_model->__call( $method, $args );
    }

    public function __toArray()
    {
        return get_object_vars( $this );
    }

    public function __toString()
    {
        return json_encode( $this );
    }

}