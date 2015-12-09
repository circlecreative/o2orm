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
class Record
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

    /**
     * Class Constructor
     *
     * @access  public
     * @final   this class can't be overwrite
     *
     * @property-write  $this->_model
     *
     * @param   Model        $model  reference of active ORM model instance
     */
    public function __construct( $model )
    {
        $this->_model = $model;
    }

    public function __set( $key, $value )
    {
        foreach(['create','update','delete'] as $action)
        {
            if( strpos($key, $action) !== FALSE )
            {
                if(empty($this->{$action}))
                {
                    $this->{$action} = new \stdClass();
                }

                $key = trim( str_replace($action, '', $key), '_');

                if(!empty($key) AND $value != '')
                {
                    if($key === 'timestamp')
                    {
                        $this->{$action}->{$key} = new \DateTime( $value );
                    }
                    elseif($key === 'user')
                    {
                        if(isset($this->_model->record_user_model))
                        {
                            if(class_exists($this->_model->record_user_model))
                            {
                                $user_model = $this->_model->record_user_model;
                                $user_model = new $user_model();
                                $user_model->set_data('id', $value);

                                $user = $user_model->find($value);

                                if(isset($user))
                                {
                                    $this->{$action}->{$key} = $user;
                                }
                                else
                                {
                                    $this->{$action}->{$key} = $value;
                                }
                            }
                        }
                        else
                        {
                            $this->{$action}->{$key} = $value;
                        }
                    }
                }

                return;
            }
        }

        $this->{$key} = $value;
    }
}