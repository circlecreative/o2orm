<?php
/**
 * Created by PhpStorm.
 * User: Steeven Andrian
 * Date: 15/12/2014
 * Time: 5:22
 */

require_once 'config.php';
require_once 'O2ORM.php';

// This is for getting an instance of O2ORM object.
// $O2 =& O2ORM::initialize();

// Set your configuration
O2ORM::set($config);

// Create connection
// param: the config name you want to connect different than default $config['test'] -> for calling O2ORM::connect('test');
O2ORM::connect();

// An options to activated schema saving to database
O2ORM::stored_schema();

// Example create books authors
$authors = O2ORM::create('authors');
$authors->set_structure = 'default';
$authors->name = ['Steeven Andrian','char','255','not null'];
$authors->website = 'http://www.steevenz.com';
$authors->biography = ['Steeven Andrian is one of legendary PHP Developer, he has made many usefull open source framework','text',0,'null'];

// Example create relationship with books authors
$books = O2ORM::create('books');
$books->set_structure = 'default';
$books->title = ['Diving into O2ORM','varchar',255,'not null'];
$books->description = ['Complete O2ORM tutorial from basic to advanced','text',0,'null'];
$books->set_relations = ['authors:id:restrict'];

// Run store schema when you finish.. or you can do O2ORM::store('authors'); this will save only authors table
O2ORM::store();

// Freeze table from being manipulated or store data
// param: no parameter will freeze all, freeze only selected table O2ORM::freeze('authors');
O2ORM::freeze();

// Reading or loading data
$books = O2ORM::read('books',1);
// for do update
$books->title = 'Testing';
O2ORM::update($books);
// for delete
O2ORM::delete($books);
