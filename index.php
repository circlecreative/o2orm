<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 5:22
 */

require_once 'config.php';
require_once 'O2ORM.php';

// Get O2ORM Instance
// $O2 =& O2ORM::initialize();

O2ORM::set($config);
O2ORM::connect();
/*O2ORM::stored_schema();

$authors = O2ORM::create('authors');
$authors->set_structure = 'default';
$authors->name = ['Steeven Andrian','char','255','not null'];
$authors->website = 'http://www.steevenz.com';
$authors->biography = ['Steeven Andrian is CEO and Founder of PT. Lingkar Kreasi, he has made many Open Source PHP Frameworks','text',0,'null'];

$books = O2ORM::create('books');
$books->set_structure = 'default';
$books->title = ['Diving into O2ORM','varchar',255,'not null'];
$books->description = ['Complete O2ORM tutorial from basic to advanced','text',0,'null'];
$books->set_relations = ['authors:id:restrict'];
//O2ORM::store();

O2ORM::store($books);*/

$books = O2ORM::read('books',1);
$books->title = 'Testing';
O2ORM::delete($books);
