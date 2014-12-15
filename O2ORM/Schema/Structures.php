<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 8:58
 */

namespace O2ORM\Schema;


class Structures
{
    public static $SelfHierarchical = array(
        'id' => ['int',11,'not-null','0'],
        'parent_id' => ['int',11,'not-null','0'],
        'created_date' => ['int',11,'null'],
        'created_user' => ['int',11,'null'],
        'modified_date' => ['int',11,'null'],
        'modified_user' => ['int',11,'null'],
        'checkin_date' => ['int',11,'null'],
        'checkin_user' => ['int',11,'null'],
        'status' => ['tinyint',1,'not-null','1'],
        'ordering' => ['int',11,'null'],
        'lft' => ['int',11,'null'],
        'rgt' => ['int',11,'null'],
        'dpt' => ['int',11,'null'],
    );

    public static $Default = array(
        'id' => ['int',11,'not-null','0','primary'],
        'created_date' => ['int',11,'null'],
        'created_user' => ['int',11,'null'],
        'modified_date' => ['int',11,'null'],
        'modified_user' => ['int',11,'null'],
        'checkin_date' => ['int',11,'null'],
        'checkin_user' => ['int',11,'null'],
        'status' => ['tinyint',1,'not-null','1'],
        'ordering' => ['int',11,'null'],
    );

    public static function CustomDefault()
    {
        $removes = func_get_args();

        $custom_default = self::$Default;

        foreach($removes as $remove)
        {
            if(in_array($remove, array('created','modified','checkin')))
            {
                unset($custom_default[$remove.'_date'], $custom_default[$remove.'_user']);
            }
            else
            {
                if(isset($custom_default[$remove])) unset($custom_default[$remove]);
            }
        }

        return $custom_default;
    }
}