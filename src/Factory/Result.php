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

namespace O2System\ORM\Factory;

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
     * @type    ORM
     */
    protected static $_model;

    /**
     * Class Constructor
     *
     * @access  public
     * @final   this class can't be overwrite
     *
     * @property-write  static::$_model
     *
     * @param   array|null $result query row result
     * @param   ORM        $model  reference of active ORM model instance
     */
    public function __construct( Model &$model, $data = array() )
    {
        static::$_model =& $model;

        if( ! empty( $data ) )
        {
            foreach( $data as $key => $value )
            {
                $this->$key = $value;
            }
        }
    }

    // ------------------------------------------------------------------------

    public function __set( $key, $value )
    {
        $references = static::$_model->mapper->get_references();

        if( is_array( $references ) )
        {
            foreach( $references as $reference_name => $reference_object )
            {
                if( strpos( $key, $reference_name ) !== FALSE )
                {
                    if( ! isset( $objects[ $reference_name ] ) )
                    {
                        $objects[ $reference_name ] = new Result( static::$_model );
                    }

                    $objects[ $reference_name ]->{str_replace( $reference_name . '_', '', $key )} = $value;
                }
                else
                {
                    $this->{$key} = $value;
                }
            }

            if( ! empty( $objects ) )
            {
                foreach( $objects as $object_name => $object_value )
                {
                    $this->{$object_name} =& $object_value;
                }
            }
        }
        else
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
    public function __get( $key )
    {
        if( property_exists( $this, $key ) )
        {
            return $this->{$key};
        }
        elseif( method_exists( static::$_model, $key ) )
        {
            static::$_model->row = $this;

            $this->{$key} = $this->__call( $key );

            return $this->{$key};
        }

        return NULL;
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
        return static::$_model->__call( $method, $args );
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