<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 8:22
 */

namespace O2ORM\Schema\Metadata;


class Table extends \O2ORM\Interfaces\TableMetadata {

    public function __construct($table, array $settings = array())
    {
        $this->name = $table;
    }
}