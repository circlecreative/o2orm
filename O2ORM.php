<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 5:18
 */

// Path to base path folder
$orm_path = pathinfo(__FILE__, PATHINFO_DIRNAME).'/';
define('O2ORM_PATH', str_replace("\\", '/', $orm_path));

set_include_path($orm_path);

function __autoload($class)
{
    $filename = str_replace('\\','/',$class).__EXT__;
    require_once(O2ORM_PATH.$filename);
}

require_once(O2ORM_PATH.'O2ORM/Config/Constants.php');
require_once(O2ORM_PATH.'O2ORM/Developer.php');
require_once(O2ORM_PATH.'O2ORM/Initializer.php');

class O2ORM extends \O2ORM\Initializer
{
    /**
     * Reference to the O2System singleton
     *
     * @var	object
     */
    private static $instance;

    /**
     * Constructor
     */
    public static function set($config)
    {
        self::$_conn = $config;
    }

    // ------------------------------------------------------------------------

    /**
     * Get the O2System singleton
     *
     * @static
     * @return	object
     */
    public static function &get_instance()
    {
        return self::$instance;
    }

    // ------------------------------------------------------------------------
}