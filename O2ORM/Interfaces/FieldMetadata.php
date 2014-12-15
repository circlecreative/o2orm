<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 7:56
 */

namespace O2ORM\Interfaces;


abstract class FieldMetadata {
    public $table;
    public $name;
    public $tick_name;
    public $param_name;
    public $alias_name;
    public $type;
    public $max_length;
    public $null = 'YES';
    public $primary_key = 'NO';
    public $foreign_key = 'NONE';
    public $references = 'NONE';
    public $constraint = 'NO';
    public $constraint_name = 'NONE';
    public $indexes = 'NO';
    public $indexes_name = 'NONE';
}