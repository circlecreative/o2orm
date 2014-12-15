<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 8:22
 */

namespace O2ORM\Interfaces;


abstract class TableMetadata {
    public $name;
    public $engine;
    public $version;
    public $rows;
    public $auto_increment = 1;
    public $created_time;
    public $updated_date;
    public $charset = 'utf8';
    public $collaction = 'utf8_unicode_ci';
    public $fields;
    public $num_fields = 0;
    public $primary_keys;
    public $foreign_keys;
    public $indexes;
    public $triggers;
    public $comments;
    public $sql_builder;
    public $sql_samples;
}