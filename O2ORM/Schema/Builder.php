<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 6:28
 */

namespace O2ORM\Schema;
use O2ORM\Drivers\MySQL\Query;
use O2ORM\Drivers\MySQL\Table;

class Builder {
    /**
     * Table Schema
     *
     * @access private
     * @var array
     */
    public static $schema = array();
    /**
     * PDO Last Run Query Stream
     *
     * @access private
     * @var string
     */
    private static $_pdo_query = NULL;
    /**
     * Last Run Query
     *
     * @access private
     * @var string
     */
    private static $_last_query = NULL;
    /**
     * Run Queries
     *
     * @access private
     * @var array
     */
    private static $_queries = array();
    private static $active_table;
    private static $query;
    private static $table;
    private static $tables = array();
    private static $structures = array();

    public function __construct($table = NULL)
    {
        self::$query = new Query();
        self::$table = new Table();

        if(! empty($table))
        {
            self::$active_table = $table;
            //self::$_schema[self::$active_table] = new \O2ORM\Schema\Metadata\Table(self::$active_table);
            /*self::$table->create($table, array(
                'id' => ['int',11,'not null']
            ));*/
        }

        return $this;
    }

    public function __call($field, $settings)
    {
        print_out($field);
    }

    public function __get($field)
    {
        call_user_func_array(array($this, $field, $params));
    }

    public function __set($field, $settings)
    {
        if (!isset(self::$schema[self::$active_table]))
        {
            $structures = \O2ORM\Schema\Structures::$Default;
            foreach ($structures as $structure_field => $structure_setting) {
                self::$schema[self::$active_table][$structure_field] = $structure_setting;
            }


        }

        if ($field === 'relations') {
            foreach ($settings as $schema_field) {
                $rel_schema = self::$schema[$schema_field];
                if (isset($rel_schema['id'])) {
                    $schema_settings = $rel_schema['id'];
                    array_pop($schema_settings);
                    self::$schema[self::$active_table][$schema_field . '_id'] = $schema_settings;
                    self::$schema[self::$active_table]['relations'][] = array(
                        'table' => $schema_field,
                        'foreign_key' => $schema_field . '_id'
                    );
                }
            }
        } else {
            self::$schema[self::$active_table][$field] = $settings;
        }

        unset(self::$schema[self::$active_table]['structure']);
    }

    public function store()
    {
        //print_out(self::$schema);
        /*$sql = $this->query->get_string();
        self::$_last_query = $sql;
        self::$_queries = $sql;

        $query = $this->pdo->prepare($sql);

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

        $query->execute();*/
    }

    private function _create_schema()
    {
        //add_column('books','author_id',['int','11','null','id']);
        if (is_array($settings)) {
            $insert_data = reset($settings);
            array_shift($settings);
        }


        $schema = new \O2ORM\Schema\Metadata\Field(self::$active_table, $field, $settings);
        self::$_schema[self::$active_table]->fields[$field] = $schema;
        self::$_schema[self::$active_table]->num_fields++;

        /*    self::$table->add_column(self::$active_table, $field, $settings);
            self::$query->insert(self::$active_table,array($field => $insert_data));
            self::$_queries[] = array(
                'sql' => self::$query->get_string(),
                'params' => self::$query->get_params()
            );



            //print_out(self::$_queries);*/
    }

    private function relations($params)
    {
        print_out($params);
    }
}