<?php
namespace O2ORM\Adapters;
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
 * Database Table Adapters Class
 *
 * @package     O2ORM
 * @subpackage  Adapters
 * @category    Adapters Class
 * @author      Steeven Andrian Salim
 * @link        http://steevenz.com
 * @link        http://circle-creative.com/products/o2orm/user-guide/adapters/table.html
 */
// ------------------------------------------------------------------------

abstract class Table
{
    public $_last_query;
    /**
     * List of valid table field types
     *
     * @var array
     * @access private
     */
    public $valid_field_types = array(
        // Numeric types
        'INT','TINYINT','SMALLINT','MEDIUMINT','BIGINT','FLOAT','DOUBLE','DECIMAL',
        // Date and Time types
        'DATE','DATETIME','TIMESTAMP','YEAR',
        // String types
        'CHAR','VARCHAR','BLOB','TEXT','TINYBLOB','TINYTEXT','MEDIUMBLOB','MEDIUMTEXT','LONGBLOB','LONGTEXT','ENUM'
    );

    public $relation_field_rules = array(
        'RESTRICT','NO ACTION','CASCADE','SET NULL'
    );

    // ------------------------------------------------------------------------

    /**
     * Create new table
     *
     * @access public
     * @param string    $table              table name
     * @param array     $fields             field name | array of fields and settings
     * @param string    $engine             engine type (capital)
     * @param bool      $auto_increment     auto increment
     * @param string    $primary_key        manual define primary key field | default first field | none for no primary key
     * @return int num of dropped tables
     */
    public function create($table, $fields, $options = array('engine' => 'MYISAM', 'increment' => TRUE, 'primary' => 'AUTO'))
    {
        if (\O2ORM::$is_connected)
        {
            $table = trim($table);
            extract($options);

            if(! empty($prefix))
            {
                $table = $prefix.'_'.$table;
            }

            $sql = 'CREATE TABLE IF NOT EXISTS '.\O2ORM\Stringer::escape($table).' (';

            // BUILD FIELDS
            foreach ($fields as $name => $settings)
            {
                $name = trim($name);
                $column = \O2ORM\Stringer::escape($name);

                $timestamp = FALSE;
                foreach($settings as $setting)
                {
                    if (in_array(strtoupper($setting), $this->valid_field_types))
                    {
                        $type = strtoupper($setting);
                        $types[$name] = $type;
                        $column.= ' '.$type;

                        if($type === 'TIMESTAMP') $timestamp = TRUE;
                    }

                    if (is_numeric($setting))
                    {
                        $column.= '('.$setting.')';
                    }

                    if (in_array(trim(strtoupper($setting)), array('NOT-NULL','NOT NULL', 'NULL')))
                    {
                        $column.= ' '.strtoupper(str_replace(array('-','_'),' ',$setting));
                    }

                    if ($setting === 'primary' OR $setting === 'pk')
                    {
                        $primary = $name;
                    }
                }

                if ($timestamp === TRUE)
                {
                    $column.= ' ON UPDATE CURRENT_TIMESTAMP';
                }

                $columns[$name] = $column;
            }

            if (isset($primary))
            {
                if(is_string($primary))
                {
                    if($primary === 'AUTO')
                    {
                        $column_keys = array_keys($columns);
                        $primary = reset($column_keys);
                    }

                    $columns[$primary] = $columns[$primary].' PRIMARY KEY';

                    if (isset($incremet) AND $increment === TRUE)
                    {
                        if($types[$primary] === 'INT') $columns[$primary] = $columns[$primary] . ' AUTO_INCREMENT';
                    }
                }
                elseif(is_array($primary))
                {
                    if (isset($incremet) AND $increment === TRUE)
                    {
                        $key = reset($primary);
                        if($types[$key] === 'INT') $columns[$key] = $columns[$key].' AUTO_INCREMENT';
                    }

                    if(! empty($primary))
                    {
                        foreach($primary as $key)
                        {
                            $columns[$key] = $columns[$key].' PRIMARY KEY';
                        }

                        $constraint = ', CONSTRAINT `pk_' . $table . '` PRIMARY KEY (' . implode(', ', $primary) . ')';
                    }
                }
            }

            if (isset($foreign))
            {
                if(is_string($foreign))
                {
                    if($primary === 'AUTO')
                    {
                        $column_keys = array_keys($columns);
                        $primary = reset($column_keys);
                    }

                    $columns[$primary] = $columns[$primary].' PRIMARY KEY';

                    if (isset($incremet) AND $increment === TRUE)
                    {
                        if($types[$primary] === 'INT') $columns[$primary] = $columns[$primary] . ' AUTO_INCREMENT';
                    }
                }
                elseif(is_array($primary))
                {
                    if (isset($incremet) AND $increment === TRUE)
                    {
                        $key = reset($primary);
                        if($types[$key] === 'INT') $columns[$key] = $columns[$key].' AUTO_INCREMENT';
                    }

                    if(! empty($primary))
                    {
                        foreach($primary as $key)
                        {
                            $columns[$key] = $columns[$key].' PRIMARY KEY';
                        }

                        $constraint = ', CONSTRAINT `pk_' . $table . '` PRIMARY KEY (' . implode(', ', $primary) . ')';
                    }
                }
            }

            $sql.= implode(', ', array_values($columns));
            $sql.= empty($constraint) ? '' : $constraint;

            $engine = (isset($engine) ? $engine : 'MYSISAM');
            $sql .= ') ENGINE = '.strtoupper($engine);

            $sql.= isset($charset) ? ' DEFAULT CHARSET='.strtolower($charset) : '';
            $sql.= isset($collate) ? ' COLLATE='.strtolower($collate) : '';
            $sql.= isset($comment) ? " COMMENT='".trim($comment)."'" : '';
            $sql.= ';';

            if(isset($return) AND $return === TRUE) return $sql;

            \O2ORM::$db->execute($sql);

            return $this->metadata($table);
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Determine if table exists
     *
     * @access public
     * @param string
     * @return bool
     */
    public function exists($table)
    {
        $sql = 'SHOW TABLES LIKE ' . "'" . $table . "'";

        if (\O2ORM::$is_connected)
        {
            $query = \O2ORM::$db->query($sql);
            if ($query AND $query->rowCount() > 0)
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Amount of tables in database
     *
     * @access public
     * @return int num of tables in database
     */
    public function num_tables()
    {
        return count($this->lists());
    }

    // ------------------------------------------------------------------------

    /**
     * List of tables names in database
     *
     * @access public
     * @return array tables names array
     */
    public function lists()
    {
        $sql = 'SHOW TABLES';

        if (\O2ORM::$is_connected)
        {
            $query = \O2ORM::$db->query($sql);
            return $query->fetchAll(\PDO::FETCH_COLUMN);
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Truncate entire tables
     *
     * @access public
     * @param array    $tables tables names
     * @return int num of truncated tables
     */
    public function truncate($tables)
    {
        if (!empty($tables) AND \O2ORM::$is_connected)
        {
            if (is_string($tables))
            {
                $tables = array($tables);
            }

            $truncated = 0;
            foreach ($tables as $table)
            {
                $query = \O2ORM::$db->query('TRUNCATE TABLE `' . trim($table) . '`', array(), TRUE);
                if ($query) $truncated++;
            }
            return $truncated;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Flush table
     *
     * @access public
     * @param array    $table table names
     * @return int num of affected rows
     */
    public function empty_table($table)
    {
        if (\O2ORM::$is_connected)
        {
            $query = \O2ORM::$db->query('DELETE FROM `' . trim($table) . '`');
            return $query->rowCount();
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Drop table
     *
     * @access public
     * @param string|array   $table   table name | array of tables names
     * @return int num of dropped tables
     */
    public function drop($tables)
    {
        if (!empty($tables) AND \O2ORM::$is_connected)
        {
            if (is_string($tables))
            {
                $tables = array($tables);
            }

            $deleted = 0;
            foreach ($tables as $table)
            {
                \O2ORM::$db->exec('DROP TABLE `' . trim($table) . '`');
                $deleted++;
            }
            return $deleted;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Rename table name
     *
     * @access public
     * @param string   $table       table name
     * @param string   $new_table   new table name
     * @return \O2ORM\Metadata table object
     */
    public function rename($table, $new_table = NULL)
    {
        if (\O2ORM::$is_connected)
        {
            \O2ORM::$db->exec('RENAME TABLE `' . trim($table) . '` TO `' . trim($new_table) . '`');
            return $this->metadata($new_table);
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Add table column
     *
     * @access public
     * @param string         $table       table name
     * @param string|array   $field       field name | field name and settings
     * @param array          $settings    field settings
     * @return \O2ORM\Metadata table object
     */
    public function add_column($table, $fields, $settings = NULL)
    {
        if (\O2ORM::$is_connected)
        {
            $sql = 'ALTER TABLE `' . trim($table) . '` ADD ';

            if (is_array($fields))
            {
                foreach($fields as $field => $settings)
                {
                    $metadata = $this->add_column($table, $field, $settings);
                }
                return $metadata;
            }
            elseif(! in_array($fields, $this->fields($table)))
            {
                $column = '`' . \O2ORM\Stringer::prepare($fields) . '` ' .
                    strtoupper($settings[0]) . '(' . $settings[1] . ') ' .
                    strtoupper(str_replace(array('not-null', 'not_null', 'not null'), 'not null', $settings[2]));

                if (isset($settings[3]))
                {
                    if (strtoupper($settings[3]) === 'FIRST')
                    {
                        $column .= ' FIRST';
                    }
                    elseif (in_array($settings[3], $this->fields($table)))
                    {
                        $column .= ' AFTER ' . $settings[3];
                    }
                    elseif (!empty($settings[3])) {
                        $column .= ' ' . $settings[3];
                    }
                }

                $sql .= $column . ';';
                \O2ORM::$db->exec($sql);

                return $this->metadata($table);
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Rename table column field name
     *
     * @access public
     * @param string $table table name
     * @param string $field field name
     * @param string $new_field new field name
     * @return \O2ORM\Metadata table object
     */
    public function rename_column($table, $field, $new_field)
    {
        $meta_field = $this->field_metadata($table, $field);
        $settings = [\O2ORM\Stringer::prepare($new_field), $meta_field->type, $meta_field->length];
        return $this->modify_column($table, $field, $settings);
    }

    // ------------------------------------------------------------------------

    /**
     * Modify table column
     *
     * @access public
     * @param string         $table       table name
     * @param string|array   $field       field name | field name and settings
     * @param array          $settings    field settings
     * @return \O2ORM\Metadata table object
     */
    public function modify_column($table, $fields, $settings = NULL)
    {
        if (\O2ORM::$is_connected)
        {
            $sql = 'ALTER TABLE `'.trim($table).'`';

            if (is_array($fields))
            {
                foreach($fields as $field => $settings)
                {
                    $metadata = $this->modify_column($table, $field, $settings);
                }
                return $metadata;
            }
            elseif(in_array($fields, $this->fields($table)))
            {

                if(! is_array($settings))
                {
                    $meta_field = $this->field_metadata($table, $fields);
                    $settings = [\O2ORM\Stringer::prepare($settings),$meta_field->type,$meta_field->length];
                }

                if(! in_array(strtoupper($settings[0]),$this->valid_field_types))
                {
                    $column = ' CHANGE `'.\O2ORM\Stringer::prepare($fields).'` `'.\O2ORM\Stringer::prepare($settings[0]).'` ';
                    array_shift($settings);
                }
                else
                {
                    $column = ' MODIFY `'.$fields.'` ';
                }

                if(in_array(strtoupper($settings[0]),$this->valid_field_types))
                {
                    $column.= strtoupper($settings[0]);
                    array_shift($settings);
                }

                // Field Types
                if(is_numeric($settings[0]))
                {
                    $column.= '('.$settings[0].')';
                    array_shift($settings);
                }

                if(! empty($settings))
                {
                    $column.= ' '.strtoupper(str_replace(array('not-null', 'not_null', 'not null'), 'not null', $settings[0]));
                }

                $sql.= $column . ';';

                \O2ORM::$db->exec($sql);

                return $this->metadata($table);
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Table primary key name
     *
     * @access public
     * @param string $table table name
     * @return string|array  primary key name or array of primary keys names
     */
    public function primary_key($table)
    {
        return $this->metadata($table)->primary_key;
    }

    /**
     * Table primary key name
     *
     * @access public
     * @param string $table table name
     * @return string|array  primary key name or array of primary keys names
     */
    public function add_primary_key($table, $field, $increment = FALSE)
    {

    }

    // ------------------------------------------------------------------------

    /**
     * Table primary key name
     *
     * @access public
     * @param string $table table name
     * @return string|array  primary key name or array of primary keys names
     */
    public function add_foreign_key($table, $reference_table, $reference_key, $foreign_key, $rules = 'cascade')
    {
        if(! is_array($rules))
        {
            $rules = array(
                'on_delete' => $rules,
                'on_update' => $rules,
            );
        }

        $sql = 'ALTER TABLE '.\O2ORM\Stringer::escape($reference_table).' ENGINE=InnoDB;';

        $sql.= 'ALTER TABLE '.\O2ORM\Stringer::escape($table);
        $sql.= ' ADD CONSTRAINT '.\O2ORM\Stringer::escape('fk_'.$reference_table);
        $sql.= ' FOREIGN KEY ('.\O2ORM\Stringer::escape($foreign_key).')';
        $sql.= ' REFERENCES '.\O2ORM\Stringer::escape($reference_table).' ('.\O2ORM\Stringer::escape($reference_key).')';

        foreach($rules as $event => $rule)
        {
            $sql.= $event === 'on_delete' ? ' ON DELETE '.strtoupper($rule) : '';
            $sql.= $event === 'on_update' ? ' ON UPDATE '.strtoupper($rule) : '';
        }

        $sql.= ';';

        \O2ORM::$db->execute($sql);

        return $this->metadata($table);
    }

    // ------------------------------------------------------------------------

    /**
     * Get table number of fields
     *
     * @access public
     * @param string $table table name
     * @return O2ORM\Metadata field object
     */
    public function field_metadata($table, $field)
    {
        if (\O2ORM::$is_connected)
        {
            $sql = 'DESCRIBE ' . $table;
            $fields = \O2ORM::$db->query($sql);
            \O2ORM\Metadata::set_type('indexes');
            $fields->setFetchMode(\PDO::FETCH_CLASS, 'O2ORM\Metadata');
            $fields_results = $fields->fetchAll();

            foreach ($fields_results as $result)
            {
                if ($result->name === $field)
                {
                    return $result;
                    break;
                }
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get all table fields name
     *
     * @access public
     * @param string   $table   table name
     * @return array|object   list of table fields name or \O2ORM\Metadata fields object
     */
    public function fields($table, $return = 'array')
    {
        if (\O2ORM::$is_connected)
        {
            if ($return === 'array')
            {
                $sql = 'DESCRIBE `' . trim($table) . '`';
                $query = \O2ORM::$db->query($sql);
                return $query->fetchAll(\PDO::FETCH_COLUMN);
            }
            else
            {
                $sql = 'DESCRIBE ' . $table;
                $query = \O2ORM::$db->query($sql);
                \O2ORM\Metadata::set_type('indexes');
                $query->setFetchMode(\PDO::FETCH_CLASS, 'O2ORM\Metadata');
                return $query->fetchAll();
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Determine if field exists on table
     *
     * @access public
     * @param string   $table   table name
     * @param string   $field   field name
     * @return bool
     */
    public function field_exists($table, $field)
    {
        if(in_array($field, $this->fields($table)))
        {
            return TRUE;
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get table number of fields
     *
     * @access public
     * @param string   $table  table name
     * @return int
     */
    public function num_fields($table)
    {
        return @count($this->fields($table));
    }

    // ------------------------------------------------------------------------

    /**
     * Add table index
     *
     * @access public
     * @param string         $table   table name
     * @param string|array   $fields  string or array of table fields
     * @return int
     */
    public function add_index($table, $fields)
    {
        if (\O2ORM::$is_connected)
        {
            if (is_array($fields))
            {
                foreach ($fields as $field)
                {
                    $metadata = $this->drop_column($table, $field);
                }
                return $metadata;
            }
            elseif(in_array($fields, $this->fields($table)))
            {
                $sql = 'ALTER TABLE `' . trim($table) . '` ADD INDEX `'.$fields.'` (`'.$fields.'`);';
                \O2ORM::$db->exec($sql);
                return $this->show_indexes($table);
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Drop table column field
     *
     * @access public
     * @param string $table table name
     * @param string $field field name
     * @return \O2ORM\Metadata table object
     */
    public function drop_column($table, $fields)
    {
        if (\O2ORM::$is_connected)
        {
            $sql = 'ALTER TABLE `' . trim($table) . '` DROP ';

            if (is_array($fields))
            {
                foreach ($fields as $field)
                {
                    $metadata = $this->drop_column($table, $field);
                }

                return $metadata;
            } elseif (in_array($fields, $this->fields($table)))
            {
                $sql .= $fields . ';';

                \O2ORM::$db->exec($sql);
                return $this->metadata($table);
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Drop table index
     *
     * @access public
     * @param string $table table name
     * @param string|array $fields string or array of table fields
     * @return int
     */
    public function drop_index($table, $fields)
    {
        if (\O2ORM::$is_connected)
        {
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    $metadata = $this->drop_column($table, $field);
                }
                return $metadata;
            } elseif (in_array($fields, $this->fields($table))) {
                $sql = 'ALTER TABLE `' . trim($table) . '` DROP INDEX `' . $fields . '`;';
                \O2ORM::$db->exec($sql);
                return $this->show_indexes($table);
            }
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Show full lists of table indexes
     *
     * @access public
     * @param string $table table name
     * @return array|object
     */
    public function show_indexes($table)
    {
        if (\O2ORM::$is_connected)
        {
            $sql = 'SHOW INDEXES FROM `' . $table . '`';
            $query = \O2ORM::$db->query($sql);
            \O2ORM\Metadata::set_type('indexes');
            $query->setFetchMode(\PDO::FETCH_CLASS, 'O2ORM\Metadata');
            return $query->fetchAll();
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * List of table indexes
     *
     * @access public
     * @param string   $table   table name
     * @return array|object
     */
    public function list_indexes($table)
    {
        if (\O2ORM::$is_connected)
        {
            $sql = 'SHOW INDEXES FROM `'.$table.'`';
            $query = \O2ORM::$db->query($sql);
            \O2ORM\Metadata::set_type('list_indexes');
            $query->setFetchMode(\PDO::FETCH_CLASS, 'O2ORM\Metadata');
            return $query->fetchAll();
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Table metadata
     *
     * @access public
     * @param string   $table   table name
     * @return \O2ORM\Metadata table object
     */
    public function metadata($table)
    {
        if (\O2ORM::$is_connected)
        {
            $table = trim($table);
            $sql = 'SHOW TABLE STATUS LIKE ' . "'" . $table . "'";
            $status = \O2ORM::$db->query($sql);
            \O2ORM\Metadata::set_type('default');
            $status->setFetchMode(\PDO::FETCH_CLASS, 'O2ORM\Metadata');

            $metadata = $status->fetch();
            $metadata->name = $table;

            $metadata->fields = $this->fields($table, 'object');
            $metadata->num_fields = count($metadata->fields);

            foreach ($metadata->fields as $result)
            {
                if ($result->primary_key === TRUE)
                {
                    $primary_keys[] = $result->name;
                }
            }

            if (!empty($primary_keys))
            {
                if (count($primary_keys) > 1)
                {
                    $metadata->primary_key = $primary_keys;
                }
                else
                {
                    $metadata->primary_key = reset($primary_keys);
                }
            }
            else
            {
                $metadata->primary_key = 'not-set';
            }

            $metadata->indexes = $this->show_indexes($table);

            return $metadata;
        }
        return FALSE;
    }

    // ------------------------------------------------------------------------

    public function optimize($table)
    {

    }

    // ------------------------------------------------------------------------

    public function repair($table)
    {

    }

    // ------------------------------------------------------------------------

    public function backup($table)
    {

    }

    // ------------------------------------------------------------------------

    public function export($table)
    {

    }
}

/* End of file Trans.php */
/* Location: ./O2ORM/Adapters/Table.php */