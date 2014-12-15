O2ORM Beta
=====
[O2ORM][2] is an Open Source Database Object Relationship Manager Tool that stores data directly in the database and creates all tables and columns required on the fly. [O2ORM][2] is currently build for MySQL only. Another amazing product from [PT. Lingkar Kreasi (Circle Creative)][1], released under MIT License.

[O2ORM][2] is build for working more powerfull with O2System Framework, but also can be used for integrated with others as standalone version with limited features.

[O2ORM][2] not just created on the fly but also store the tables information schema into database, it will become very useful in the future.

[O2ORM][2] is insipired by [RedBeanPHP][4] and [CodeIgniter][3] Active Records concept, so [O2ORM][2] is has also functionality similar with them, but a little bit different at the syntax.

Example
----
Independent Table
```
require_once(O2ORM.php);
O2ORM::set($config);
O2ORM::connect();

$products = O2ORM::create('products');
$products->structure = 'default'; // create default structured fields
$products->name = ['O2System','varchar','255','not null'];
$products->website = 'http://www.circle-creative.com/products/o2system'; // stored as VARCHAR(255) NULL field
$products->description = ['text','null'] // stored as TEXT(0) NULL field
O2ORM::store();
```
-> Table schema has: id,created_date,created_user,modified_date,modified_user,status,ordering and etc.

Self Hierarchical (Based on MySQL Hierarchical Data)
----
```
$nav = O2ORM::create('navigations');
$nav->structure = 'self-hierarchical';
$nav->title = 'Home';
$nav->URL = 'http://www.circle-creative.com/products/o2orm';
```
-> Table schema has: id,parent_id,lft,rgt,dpt,ordering and etc as default for nested recursive used or nested with no recursive based on dpt(depth).


Has Relationship
```
require_once(O2ORM.php);
O2ORM::set($config);
O2ORM::connect();

$authors = O2ORM::create('authors');
$authors->structure = 'default';
$authors->name = ['Steeven Andrian','char','255','not null'];
$authors->website = 'http://www.steevenz.com';
$authors->biography = ['Steeven Adrian is one of the legendary PHP Developer, who has made a lot of well-known Open Source PHP Frameworks.','text',0,'null'];
O2ORM::store();

$books = O2ORM::create('books');
$books->structure = 'default';
$books->relations = ['authors:1'];
$books->title = ['Diving into O2ORM','varchar',255,'not null'];
$books->description = ['Complete O2ORM tutorial from basic to advanced','text',0,'null'];
O2ORM::store();
```
-> Table schema built with constrain foreign key books will has authors_id as FK.

Query Active Record Builder
----
```
$DB = O2ORM::Query();
$DB->escape()
   ->select(array(
        'book.title' => 'title',
        'author.name' => 'author'
      ))
   ->from('book')
   ->join(array(
        'author' => array(
            'author.id' => 'book.author_id'
        ),
        'left:book_category' => array(
            'book_category.book_id' => 'book.id',
        ),
        'left:category' => array(
            'book_category.category_id' => 'category.id'
        )
      )
    ->where(array(
        'book.id:equal' => 'int:1',
        'author.name:not' => 'str:steevenz:8'
    ))
    ->where_between('book.price',array(10000,20000))
    ->having('sum:book.price:greater','int:5000')
    ->or_having('book.price:greater','int:10000');

$query = $DB->get();
$query->result(); // Return O2ORM\DB\Results Objects
$query->result('array') // Return as array
```
-> The results object will automaticly unserialize and json_decode() the field which is has serialized array or JSON encode data value.

Features
----
- PDO Wrapper
- PSR-0 Autoloader
- Schema Builder
- Query Active Record (Has similar functionality with CodeIgniter Active Record +  Some new features)
- Table Active Record (Has similar functionality with CodeIgniter Active Record +  Some new features)
- Query Result Modifier (can be extended) based on PDO::FETCH_CLASS
- Metadata of Tables, Columns, and Fields
- Information Schema Storage
- O2DPO Debugger Tools (Developer Print Out)
- Serialize Array and JSON Storage Support

More details at the Wiki. (Coming Soon)
Coming Soon for Testing

Ideas and Suggestions
---------------------
Please kindly mail us at [developer@circle-creative.com][6] or [steeven@circle-creative.com][7].

Bugs and Issues
---------------
Please kindly submit your issues at Github so we can track all the issues along development.

System Requirements
-------------------
- PHP 5.2+
- Composer
- PDO

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim (steevenz.com)][8]

Special Thanks
--------------
* My Lovely Wife zHa,My Little Princess Angie, My Little Prince Neal - Thanks for all your supports, i love you all
* Viktor Iwan Kristanda (PT. Doxadigital Indonesia)
* Arthur Purnama (CGI Deutschland - Germany)
* Ariza Novisa (eClouds Center - Indonesia)

[1]: http://www.circle-creative.com
[2]: http://www.circle-creative.com/products/o2orm
[3]: http://www.codeigniter.com
[4]: http://www.redbeanphp.com
[5]: http://www.bcit.ca/cas/computing/
[6]: mailto:developer@circle-creative.com
[7]: mailto:steeven@circle-creative.com
[8]: http://cv.steevenz.com
[9]: http://www.smarty.net/
[10]: http://twig.sensiolabs.org/
[11]: https://getcomposer.org
[12]: https://packagist.org/packages/o2system/o2system
