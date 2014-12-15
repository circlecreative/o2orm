<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 8:01
 */

namespace O2ORM\Schema\Metadata;


class Field extends \O2ORM\Interfaces\FieldMetadata {

    public function __construct($table, $field, array $schema)
    {
        $this->table = $table;
        $this->name = $field;
        $this->tick_name = '`'.$field.'`';
        $this->param_name = ':'.$field;
        $this->alias_name = $table.'.'.$field;
        $this->type = strtoupper($schema[0]);
        $this->max_length = $schema[1];
        $this->null = (strtoupper($schema[2]) == 'NULL' ? 'YES' : 'NO');
        $this->primary_key = 'NO';
    }

}