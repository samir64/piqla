# Pinq v1.0.0

This version of Pinq just supports "Pinq to array"
And in first release it has just `select` function to select some elements (like as `SELECT` in SQL)

## How to use

Define an array and pass that to new Pinq instance constructor
Know you can use Pinq deformer functions and call them to result again and again and ...

## Sample

```php
$var = [
   ["name" => "jack", "family" => "Gonjishke", "age" => 45],
   ["name" => "joe", "family" => "gandomi", "age" => 32],
   ["name" => "john", "family" => "val john", "age" => 63]
];

$var = new Pinq($var);
$result = $var->select(
   ["name, age, family" => function ($n, $a, $f) {
      if ($a > 40) {
         return ["family" => $f, "parent" => "george"];
      }
   }]
);

var_dump($result->toArray());
echo;
var_dump($result->parent->toArray());

/* Output:
array(2) {
   [0]=> array(2) {
      ["family"]=> string(9) "Gonjishke"
      ["parent"]=> string(6) "george"
   }
   [1]=> array(2) {
      ["family"]=> string(8) "val john"
      ["parent"]=> string(6) "george"
   }
}

array(2) {
   [0]=> array(1) {
      ["parent"]=> string(6) "george"
   }
   [1]=> array(1) {
      ["parent"]=> string(6) "george"
   }
} 
*/
```
