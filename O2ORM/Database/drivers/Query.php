<?php
namespace O2System\Core\Database;
/**
 * O2System
 *
 * An open source application development framework for PHP 5.2.4 or newer
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
 * @package     O2System
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Query Builder Class
 *
 * @package     O2System
 * @subpackage  system/core
 * @category    Core Class
 * @author      Steeven Andrian Salim
 * @link        http://circle-creative.com/products/o2system/user-guide/core/database/query.html
 */

class Query extends \O2System\Core\Driver
{
    private $_escape = FALSE;
    private $_distinct = FALSE;
    private $_union;

    private $_table;
    private $_tables, $_select, $_from, $_join, $_order_by, $_group_by, $_params = array();

    private $_limit;
    private $_offset;

    private $_query = NULL;

    private $_num_rows;

    private $_having, $_or_having = array();

    private $_having_clauses = array(
        'HAVING' => '_having',
        'OR-HAVING' => '_or_having',
    );

    private $_where, $_or_where = array();

    private $_where_clauses = array(
        'WHERE' => '_where',
        'OR' => '_or_where',
        'IN' => '_where',
        'OR-IN' => '_or_where',
        'NOT-IN' => '_where',
        'OR-NOT-IN' => '_or_where'
    );

    private $_like_statements = array(
        'LIKE' => '_where',
        'OR-LIKE' => '_or_where',
        'NOT-LIKE' => '_where',
        'OR-NOT-LIKE' => '_or_where',
    );

    private $_operators = array(
        'equal' => '=',
        'not' => '!=',
        'greater' => '>',
        'less' => '<',
        'greater_equal' => '>=',
        'less_equal' => '<='
    );

    private $_math_functions = array(
        'min','max','sum','avg','count'
    );

    private $_pdo_params = array(
        'null' => \PDO::PARAM_NULL,
        'int' => \PDO::PARAM_INT,
        'str' => \PDO::PARAM_STR,
        'lob' => \PDO::PARAM_LOB,
        'stmt' => \PDO::PARAM_STMT,
        'bool' => \PDO::PARAM_BOOL,
        'io' => \PDO::PARAM_INPUT_OUTPUT
    );

    public function run($query)
    {
        $this->_query = $query;
    }

    public function get_string()
    {
        if(empty($this->_query)) $this->prepare();
        return $this->_query;
    }

    public function get_params()
    {
        if(empty($this->_query)) $this->prepare();
        return $this->_params;
    }

    public function prepare()
    {
        // SELECT
        $query = 'SELECT ';

        // DISTINCT
        if($this->_distinct === TRUE) $query.= 'DISTINCT ';
        $query.= empty($this->_select) ? '*' : implode(', ', array_unique($this->_select));

        // FROM
        $query.= ' FROM '.$this->_table;

        // JOIN
        if(! empty($this->_join)) $query.= ' '.implode(' ',$this->_join);

        // WHERE
        if(! empty($this->_where)) $query.= ' WHERE '.implode(' AND ', $this->_where);
        if(! empty($this->_or_where)) $query.= ' OR '.implode(' OR ', $this->_or_where);

        // WHERE IN
        if(! empty($this->_where_in)) $query.= ' AND '.implode(' ', $this->_where_in);
        if(! empty($this->_or_where_in)) $query.= ' OR '.implode(' ', $this->_or_where_in);

        // WHERE NOT IN
        if(! empty($this->_where_not_in)) $query.= ' AND '.implode(' ', $this->_where_not_in);
        if(! empty($this->_or_where_not_in)) $query.= ' OR '.implode(' ', $this->_or_where_not_in);

        // GROUP
        if(! empty($this->_group_by)) $query.= ' GROUP BY '.implode(', ',$this->_group_by);

        // HAVING
        if(! empty($this->_having)) $query.= ' HAVING '.implode(', ',$this->_having);
        if(! empty($this->_or_having)) $query.= ' OR '.implode(', ',$this->_or_having);

        // UNION
        if(! empty($this->_union)) $query.= ' UNION '.$this->_union;

        // ORDERING
        if(! empty($this->_order_by)) $query.= ' ORDER BY '.implode(', ',$this->_order_by);

        // LIMIT, OFFSET
        if(! empty($this->_limit)) $query.= ' LIMIT '.(int) $this->_limit;
        if(! empty($this->_offset)) $query.= ', '.(int) $this->_offset;

        $this->_query = $query;
        return $this;
    }
    
    /**
     * Get
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param	string	table name
     * @param	int  	limit query
     * @param   int     offset query
     * @return	object
     */
    public function get($table = NULL, $limit = NULL, $offset = NULL)
    {
        if(!empty($table)) $this->from($table);

        $this->_limit = $limit;
        $this->_offset = $offset;

        $this->prepare();

        return $this;
    }

    /**
     * Get Where
     *
     * Compiles the select statement based on the other functions called
     * and runs the query
     *
     * @param	string	table name
     * @param	array	conditions
     * @param	int  	limit query
     * @param   int     offset query
     * @return	object
     */
    public function get_where($table = NULL, $conditions = array(), $limit = NULL, $offset = NULL)
    {
        if(!empty($table)) $this->from($table);

        $this->where($conditions);

        $this->_limit = $limit;
        $this->_offset = $offset;

        $this->prepare();

        return $this;
    }

    public function escape($escape = TRUE)
    {
        $this->_escape = $escape;
        return $this;
    }

    private function __prepare_string($string)
    {
        if(strpos($string,'.') !== FALSE)
        {
            $x_strings = explode('.', $string);

            // Collects Tables
            $this->_tables[] = reset($x_strings);
            $this->_tables = array_unique($this->_tables);

            $_string = array();
            foreach($x_strings as $x_string)
            {
                $_string[] = $this->__prepare_string($x_string);
            }

            $string = implode('.', $_string);
        }
        else
        {
            $string = $this->_escape === TRUE ? '`'.trim($string).'`' : trim($string);
        }

        return $string;
    }

    private function __flatten($array)
    {
        if(! empty($array))
        {
            $array = array_map(
                function($string)
                {
                    return "'".$string."'";
                }, $array
            );

            return implode(',',$array);
        }

        return NULL;
    }

    public function union($query)
    {
        $this->_union = $query;
    }

    /**
     * Select
     *
     * Generates the SELECT portion of the query
     *
     * @param	string
     * @return	O2System Database::Query
     */
    public function select($select)
    {
        if (is_string($select))
        {
            $select = preg_split('[,]', $select, -1, PREG_SPLIT_NO_EMPTY);
        }

        foreach ($select as $field => $alias)
        {
            if(is_numeric($field))
            {
                $this->_select[] = $this->__prepare_string($alias);
            }

            if(is_string($field))
            {
                $this->_select[] = $this->__prepare_string($field).' AS '.$this->__prepare_string($alias);
            }
        }

        return $this;
    }

    /**
     * Select Max
     *
     * Generates a SELECT MAX(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	O2System Database::Query
     */
    public function select_max($field, $alias = NULL)
    {
        $select = 'MAX('.$this->__prepare_string($field).')';
        $select.= empty($alias) ? '' : ' '.$this->__prepare_string($alias);
        $this->_select[] = $select;
        return $this;
    }

    /**
     * Select Min
     *
     * Generates a SELECT MIN(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	O2System Database::Query
     */
    public function select_min($field, $alias = NULL)
    {
        $select = 'MIN('.$this->__prepare_string($field).')';
        $select.= empty($alias) ? '' : ' '.$this->__prepare_string($alias);
        $this->_select[] = $select;
        return $this;
    }

    /**
     * Select Average
     *
     * Generates a SELECT AVG(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	O2System Database::Query
     */
    public function select_avg($field, $alias = NULL)
    {
        $select = 'AVG('.$this->__prepare_string($field).')';
        $select.= empty($alias) ? '' : ' '.$this->__prepare_string($alias);
        $this->_select[] = $select;
        return $this;
    }

    /**
     * Select Sum
     *
     * Generates a SELECT SUM(field) portion of a query
     *
     * @param	string	the field
     * @param	string	an alias
     * @return	O2System Database::Query
     */
    public function select_sum($field, $alias = NULL)
    {
        $select = 'SUM('.$this->__prepare_string($field).')';
        $select.= empty($alias) ? '' : ' '.$this->__prepare_string($alias);
        $this->_select[] = $select;
        return $this;
    }

    /**
     * DISTINCT
     *
     * Sets a flag which tells the query string compiler to add DISTINCT
     *
     * @param	bool
     * @return	O2System Database::Query
     */
    public function distinct($distinct = TRUE)
    {
        $this->_distinct = $distinct;
        return $this;
    }

    /**
     * From
     *
     * Generates the FROM portion of the query
     *
     * @param	mixed	can be a string or array
     * @return	O2System Database::Query
     */
    public function from($table, $reset = FALSE)
    {
        $this->_table = $this->__prepare_string($table);

        if($reset === TRUE)
        {
            $this->_from = array();
        }

        $this->_from[] = $this->_table;

        // Collect Tables
        $this->_tables[] = $this->_table;
        $this->_tables = array_unique($this->_tables);

        return $this;
    }

    /**
     * Join
     *
     * Generates the JOIN portion of the query
     *
     * @param	string
     * @param	string	the join condition
     * @param	string	the type of join
     * @return	object
     */
    public function join($tables)
    {
        $query = array();
        foreach($tables as $table => $conditions)
        {
            if(strpos($table,':') !== FALSE)
            {
                $x_table = preg_split('[:]',$table,-1,PREG_SPLIT_NO_EMPTY);

                $table = end($x_table);
                array_pop($x_table);
                array_push($x_table,'join');

                $type = array_map('strtoupper',$x_table);
                $type = implode(' ',$type);

                $query[] = $type.' '.$table.' ON '.$this->__prepare_string(key($conditions)).' = '.$this->__prepare_string($conditions[key($conditions)]);
            }
            else
            {
                $query[] = 'JOIN '.$table.' ON '.$this->__prepare_string(key($conditions)).' = '.$this->__prepare_string($conditions[key($conditions)]);
            }
        }

        $this->_join = $query;

        return $this;
    }

    /**
     * GROUP BY
     *
     * @param	string	$by
     * @return	O2System Database::Query
     */
    public function group_by($by)
    {
        $this->_group_by[] = $this->__prepare_string($by);
    }

    /**
     * Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function where($fields, $value = NULL, $clause = 'WHERE')
    {
        if(is_array($fields))
        {
            foreach($fields as $field => $value)
            {
                $this->where($field, $value, $clause);
            }
        }
        else
        {
            // Prepare WHERE Condition
            $operator = '=';
            if(strpos($fields,':') !== FALSE)
            {
                $x_fields = explode(':', $fields);

                $fields = reset($x_fields);

                if(in_array(reset($x_fields), $this->_math_functions))
                {
                    $math_function = reset($x_fields);
                    array_shift($x_fields);
                    $fields = reset($x_fields);
                }

                $operator = end($x_fields);
                $operator = isset($this->_operators[$operator]) ? $this->_operators[$operator] : '=';
            }

            $parameter = $fields;
            if(strpos($fields,'.') !== FALSE)
            {
                $x_fields = explode('.', $fields);
                $parameter = end($x_fields);
            }
            $parameter = ':'.$parameter;

            if(isset($math_function))
            {
                $fields = strtoupper($math_function).'('.$this->__prepare_string($fields).')';
            }
            else
            {
                $fields = $this->__prepare_string($fields);
            }

            // Prepare WHERE Value
            if(strpos($value,':') !== FALSE)
            {
                $x_value = explode(':',$value);
                $param_type = reset($x_value);
                array_shift($x_value);

                if(count($x_value) == 2)
                {
                    $param_length = end($x_value);
                    if (is_numeric($param_length))
                    {
                        array_pop($x_value);
                    }
                }

                $value = end($x_value);
            }

            if(isset($this->_where_clauses[$clause]))
            {
                $_clause = $this->_where_clauses[$clause];
                $this->{$_clause}[] = $fields . ' ' . $operator . ' ' . $parameter;
                $this->{$_clause} = array_unique($this->{$_clause});

                // Bind Params
                $params = new \stdClass();
                $params->name = $parameter;
                $params->field = substr($parameter, 1);
                $params->value = $value;

                if (isset($param_type) AND isset($this->_pdo_params[$param_type]))
                {
                    $params->type = $this->_pdo_params[$param_type];
                }

                if (isset($param_length))
                {
                    $params->length = $param_length;
                }

                $this->_params[] = $params;
            }
        }

        return $this;
    }

    /**
     * OR Where
     *
     * Generates the WHERE portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function or_where($field, $value = NULL)
    {
        $this->where($field, $value, 'OR');
        return $this;
    }

    /**
     * Where_in
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * AND if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function where_in($field, $values = NULL)
    {
        $this->_where[] = $this->__prepare_string($field).' IN ('.$this->__flatten($values).')';
        return $this;
    }

    /**
     * Where_in_or
     *
     * Generates a WHERE field IN ('item', 'item') SQL query joined with
     * OR if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function or_where_in($field, $values = NULL)
    {
        $this->_or_where[] = $this->__prepare_string($field).' IN ('.$this->__flatten($values).')';
        return $this;
    }

    /**
     * Where_not_in
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with AND if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function where_not_in($field, $values = NULL)
    {
        $this->_where[] = $this->__prepare_string($field).' NOT IN ('.$this->__flatten($values).')';
        return $this;
    }

    /**
     * Where_not_in_or
     *
     * Generates a WHERE field NOT IN ('item', 'item') SQL query joined
     * with OR if appropriate
     *
     * @param	string	The field to search
     * @param	array	The values searched on
     * @return	object
     */
    public function or_where_not_in($field, $values = NULL)
    {
        $this->_or_where[] = $this->__prepare_string($field).' NOT IN ('.$this->__flatten($values).')';
        return $this;
    }

    public function where_between($field, array $values = array())
    {
        $this->_where[] = $this->__prepare_string($field).' BETWEEN '.implode(' AND ', $values);
        return $this;
    }

    public function or_where_between($field, array $values = array())
    {
        $this->_or_where[] = $this->__prepare_string($field).' BETWEEN '.implode(' AND ', $values);
        return $this;
    }

    public function where_not_between($field, array $values = array())
    {
        $this->_where[] = $this->__prepare_string($field).' NOT BETWEEN '.implode(' AND ', $values);
        return $this;
    }

    public function or_where_not_between($field, array $values = array())
    {
        $this->_or_where[] = $this->__prepare_string($field).' NOT BETWEEN '.implode(' AND ', $values);
        return $this;
    }

    /**
     * Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function like($fields, $match = '', $side = 'both', $statement = 'LIKE')
    {
        if(is_array($fields))
        {
            foreach($fields as $field => $match)
            {
                $this->like($field, $match, $side, $statement);
            }
        }

        $query = $this->__prepare_string($fields).' '.str_replace(array('OR-LIKE','OR-NOT-'),array('LIKE','NOT '),$statement).' ';

        switch($side)
        {
            default:
            case 'both':
                $query.= "'%".$match."%'";
                break;
            case 'before':
                $query.= "'%".$match."'";
                break;
            case'after':
                $query.= "'".$match."%'";
                break;
            case'none':
                $query.= "'".$match."'";
        }

        if(isset($this->_like_statements[$statement]))
        {
            $_statement = $this->_like_statements[$statement];
            $this->{$_statement}[] = $query;
            $this->{$_statement} = array_unique($this->{$_statement});
        }

        return $this;
    }

    /**
     * OR Like
     *
     * Generates a %LIKE% portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function or_like($field, $match = '', $side = 'both')
    {
        $this->like($field, $match, $side, 'OR-LIKE');
        return $this;
    }

    /**
     * Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with AND
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function not_like($field, $match = '', $side = 'both')
    {
        $this->like($field, $match, $side, 'NOT-LIKE');
        return $this;
    }

    /**
     * OR Not Like
     *
     * Generates a NOT LIKE portion of the query. Separates
     * multiple calls with OR
     *
     * @param	mixed
     * @param	mixed
     * @return	object
     */
    public function or_not_like($field, $match = '', $side = 'both')
    {
        $this->like($field, $match, $side, 'OR-NOT-LIKE');
        return $this;
    }

    /**
     * Sets the HAVING value
     *
     * Separates multiple calls with AND
     *
     * @param	string
     * @param	string
     * @return	object
     */
    public function having($fields, $value = '', $clause = 'HAVING')
    {
        if(is_array($fields))
        {
            foreach($fields as $field => $value)
            {
                $this->where($field, $value, $clause);
            }
        }
        else
        {
            // Prepare HAVING Condition
            $operator = '=';
            if(strpos($fields,':') !== FALSE)
            {
                $x_fields = explode(':', $fields);

                $fields = reset($x_fields);

                if(in_array(reset($x_fields), $this->_math_functions))
                {
                    $math_function = reset($x_fields);
                    array_shift($x_fields);
                    $fields = reset($x_fields);
                }

                $operator = end($x_fields);
                $operator = isset($this->_operators[$operator]) ? $this->_operators[$operator] : '=';
            }

            $parameter = $fields;
            if(strpos($fields,'.') !== FALSE)
            {
                $x_fields = explode('.', $fields);
                $parameter = end($x_fields);
            }
            $parameter = ':'.$parameter;

            if(isset($math_function))
            {
                $fields = strtoupper($math_function).'('.$this->__prepare_string($fields).')';
            }
            else
            {
                $fields = $this->__prepare_string($fields);
            }

            // Prepare HAVING Value
            if(strpos($value,':') !== FALSE)
            {
                $x_value = explode(':',$value);
                $param_type = reset($x_value);
                array_shift($x_value);

                if(count($x_value) == 2)
                {
                    $param_length = end($x_value);
                    if (is_numeric($param_length))
                    {
                        array_pop($x_value);
                    }
                }

                $value = end($x_value);
            }

            if(isset($this->_having_clauses[$clause]))
            {
                $_clause = $this->_having_clauses[$clause];
                $this->{$_clause}[] = $fields . ' ' . $operator . ' ' . $parameter;
                $this->{$_clause} = array_unique($this->{$_clause});

                // Bind Params
                $params = new \stdClass();
                $params->name = $parameter;
                $params->field = substr($parameter, 1);
                $params->value = $value;

                if (isset($param_type) AND isset($this->_pdo_params[$param_type]))
                {
                    $params->type = $this->_pdo_params[$param_type];
                }

                if (isset($param_length))
                {
                    $params->length = $param_length;
                }

                $this->_params[] = $params;
            }
        }

        return $this;
    }

    /**
     * Sets the OR HAVING value
     *
     * Separates multiple calls with OR
     *
     * @param	string
     * @param	string
     * @return	object
     */
    public function or_having($field, $value = '')
    {
        $this->having($field, $value, 'OR-HAVING');
    }

    public function insert()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }
}

/* End of file Query.php */
/* Location: ./system/core/Database/drivers/Query.php */