<?php
namespace O2ORM\Schema;
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
 * Database Schema Mappers]
 *
 * @package       O2ORM
 * @subpackage    Schema
 * @category      Schema Class
 * @author        Steeven Andrian Salim
 * @link          http://circle-creative.com/products/o2orm/user-guide/schema/mapper.html
 */
// ------------------------------------------------------------------------

use O2ORM\Drivers\MySQL\Query;
use O2ORM\Drivers\MySQL\Table;

class Mapper
{
    /**
     * Freeze schema flag
     *
     * @access protected
     * @var array
     */
    protected static $_is_freezed = FALSE;

    /**
     * Store schema flag
     *
     * @access protected
     * @var array
     */
    protected static $_store_schema = FALSE;

    /**
     * Table Schema
     *
     * @access protected
     * @var array
     */
    protected static $_schema = array();

    /**
     * Table Active
     *
     * @access public
     * @var string
     */
    public $table;

    /**
     * Create Table Schema
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function create($table, $options = array('engine' => 'MyISAM', 'increment' => TRUE, 'primary' => 'AUTO'))
    {
        $this->table = $table;
        self::$_schema[$table] = (object) $options;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Magic method to get all sets variables
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function __set($field, $options)
    {
        if($field === 'set_structure')
        {
            $this->set_structure($this->table, $options);
        }
        elseif($field === 'set_relations')
        {
            $this->set_relations($this->table, $options);
        }
        elseif($field === 'set_primary')
        {
            $this->set_charset($this->table, $options);
        }
        elseif($field === 'set_increment')
        {
            $this->set_increment($this->table, $options);
        }
        elseif($field === 'set_engine')
        {
            $this->set_engine($this->table, $options);
        }
        elseif($field === 'set_charset')
        {
            $this->set_charset($this->table, $options);
        }
        elseif($field === 'set_comment')
        {
            $this->set_comment($this->table, $options);
        }
        else
        {
            $this->set_field($this->table, $field, $options);
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Structure
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_structure($table, $structure = 'default')
    {
        switch($structure)
        {
            default:
            case 'default':
                $fields = \O2ORM\Schema\Structures::$default;
                break;
            case 'self-hc':
            case 'self-hierarchical':
                $fields = \O2ORM\Schema\Structures::$self_hierarchical;
                break;
        }

        foreach($fields as $field => $options)
        {
            if(strpos(end($options), ':') != FALSE)
            {
                $x_sample_data = explode(':',end($options));
                $sample_data[$field] = str_replace(array('null-timestamp', 'timestamp','unix-timestamp'),array('0000-00-00 00:00:00', date('Y-m-d H:i:s'), strtotime(date('Y-m-d H:i:s'))),end($x_sample_data));

                array_pop($options);
            }
            elseif(end($options) === 'primary')
            {
                $increment = (reset($options) === 'int' ? TRUE : FALSE);
                $this->set_primary($table, $field, $increment);
                array_pop($options);
            }

            $_fields[$field] = $options;
        }

        @self::$_schema[$table]->fields = $_fields;

        if(! empty($sample_data))
        {
            @self::$_schema[$table]->sample_data = $sample_data;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Settings
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_table($table, array $options = array('engine' => 'MyISAM', 'increment' => TRUE, 'primary' => 'AUTO'))
    {
        foreach($options as $key => $value)
        {
            @self::$_schema[$table]->{$key} = $value;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Field
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_field($table, $field, $options)
    {
        if(is_string($options))
        {
            $sample_data = $options;

            if(strlen($options) < 255)
            {
                $options = ['varchar',255,'null'];
            }
            elseif(strlen($options) > 255)
            {
                $options = ['text','null'];
            }
        }
        elseif(is_numeric($options))
        {
            $sample_data = $options;
            $options = ['int',11,'null'];
        }
        elseif(is_array($options))
        {
            $sample_data = reset($options);

            if(! in_array(strtoupper($sample_data), \O2ORM::$db->table->valid_field_types))
            {
                array_shift($options);
            }
            else
            {
                $sample_data = NULL;
            }
        }

        @self::$_schema[$table]->fields[$field] = $options;
        @self::$_schema[$table]->sample_data[$field] = $sample_data;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Relations
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_relations($table, $relations = array())
    {
        $relations_rules =  \O2ORM::$db->table->relation_field_rules;

        foreach($relations as $relation)
        {
            $field = 'id'; $sample_data = 1; $on_delete = 'cascade'; $on_update = 'cascade';

            if(strpos($relation,':') !== FALSE)
            {
                $x_relation = explode(':',$relation);

                $parent_table = reset($x_relation);
                array_shift($x_relation);

                $parent_fields = self::$_schema[$parent_table]->fields;
                $parent_fields = array_keys($parent_fields);

                if(in_array(reset($x_relation), $parent_fields))
                {
                    $field = reset($x_relation);
                    array_shift($x_relation);
                }

                if(in_array(strtoupper(reset($x_relation)), $relations_rules))
                {
                    $on_delete = reset($x_relation);
                    $on_update = reset($x_relation);
                    array_shift($x_relation);
                }

                if(! empty($x_relation))
                {
                    $sample_data = reset($x_relation);
                }
            }
            else
            {
                $parent_table = $relation;
            }

            $parent_key = $field;
            $field = $parent_table.'_'.$field;

            $relations_schema[] = array(
                'references_table' => $parent_table,
                'references_key' => $parent_key,
                'foreign_key' => $field,
                'on_delete' => $on_delete,
                'on_update' => $on_update,
            );

            $this->set_engine($table, 'InnoDB');
            $this->set_field($table, $field, $sample_data);
        }

        self::$_schema[$table]->relations_schema = $relations_schema;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Engine
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_engine($table, $engine = 'MyISAM')
    {
        self::$_schema[$table]->engine = $engine;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Charset
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_charset($table, $charset)
    {
        self::$_schema[$table]->charset = $charset;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Collate
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_collate($table, $collate)
    {
        self::$_schema[$table]->collate = $collate;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Primary
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_primary($table, $field, $increment = TRUE)
    {
        $primary = self::$_schema[$table]->primary;

        if(is_string($primary) AND $primary === 'AUTO')
        {
            $primary = $field;
        }
        elseif(is_string($primary) AND $primary !== 'AUTO')
        {
            $primary = [$primary, $field];
        }
        elseif(is_array($primary))
        {
            array_push($primary, $field);
        }

        self::$_schema[$table]->primary = $primary;
        self::$_schema[$table]->increment = $increment;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Increment
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_increment($table, $increment = TRUE)
    {
        self::$_schema[$table]->increment = $increment;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Table Comment
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function set_comment($table, $comment = '')
    {
        self::$_schema[$table]->comment = $comment;
        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Freeze schema to prevent storing
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function freeze($freeze = TRUE)
    {
        if(is_bool($freeze) AND $freeze === TRUE)
        {
            self::$_is_freezed = $freeze;
        }
        elseif(is_string($freeze))
        {
            @self::$_schema[$freeze]->freeze = TRUE;
        }
        elseif(is_array($freeze))
        {
            foreach($freeze as $table)
            {
                @self::$_schema[$table]->freeze = TRUE;
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Store Schema Map
     *
     * @access public
     * @return \O2ORM\Mapper Object
     */
    public function store($object = NULL)
    {
        if(! empty($object))
        {
            $schema[$object->table] = self::$_schema[$object->table];
        }
        else
        {
            $schema = self::$_schema;
        }

        if(self::$_is_freezed === FALSE)
        {
            foreach($schema as $table => $settings)
            {
                if(isset($settings->freeze) AND $settings->freeze === TRUE) continue;

                // Create Table
                \O2ORM::$db->table->create($table,$settings->fields, array(
                    'engine' => $settings->engine,
                    'increment' => $settings->increment,
                    'primary' => $settings->primary,
                    'charset' => $settings->charset,
                    'collate' => $settings->collate,
                    'comment' => @$settings->comment
                ));

                \O2ORM::$db->insert($table, $settings->sample_data);

                if(! empty($settings->relations_schema))
                {
                    foreach($settings->relations_schema as $relation)
                    {
                        \O2ORM::$db->table->add_foreign_key(
                            $table,
                            $relation['references_table'],
                            $relation['references_key'],
                            $relation['foreign_key'],
                            array(
                                'on_delete' => $relation['on_delete'],
                                'on_update' => $relation['on_update']
                            ));
                    }
                }
            }
        }

        return $this;
    }
}

/* End of file Mapper.php */
/* Location: ./O2ORM/Schema/Mapper.php */