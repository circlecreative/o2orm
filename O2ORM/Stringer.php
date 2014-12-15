<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 6:16
 */

namespace O2ORM;


class Stringer
{
    public static function prepare($string, $delimiter = '_')
    {
        if (!empty($string) or $string != '')
        {
            $patterns = array(
                '/[\s]+/',
                '/[^a-zA-Z0-9_-\s]/',
                '/[_]+/',
                '/[-]+/',
                '/-/',
                '/[' . $delimiter . ']+/'
            );
            $replace = array(
                '-',
                '-',
                '-',
                '-',
                $delimiter,
                $delimiter
            );
            $string = preg_replace($patterns, $replace, $string);
            return trim($string);
        }
        return false;
    }

    public static function flatten($array)
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
}