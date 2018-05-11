# Pinq v1.1.15

This version of Pinq just supports "Pinq to array"

## Available methods

```php
where(callable ...$params)
select(callable ...$params)
delete(callable ...$params)
update(callable ...$params)
insert(callable ...$params)
distinct()
count()
limit(int $count, int $offset)
offset(int $offset)
```

## How to use

Define an array and pass that to new Pinq instance constructor

Now you can use Pinq deformer functions and call them for returned result again and again and ...

## Sample

```php
$var =
   [
      [
         "name" => "jack",
         "family" => "Gonjishke",
         "age" => 45
      ],
      [
         "name" => "joe",
         "family" => "gandomi",
         "age" => 32
      ],
      [
         "name" => "john",
         "family" => "val john",
         "age" => 63
      ]
   ];

$var = new Pinq($var);
$result = $var->select(
   [function ($family, $age) {
      if ($age > 40) {
         return ["family" => $family, "parent" => "george"];
      }
   },
   function ($family, $age) {
      if ($age < 40) {
         return ["family" => $family, "parent" => "sam"];
      }
   }]
);

print_r($result->toArray());
echo "<br><br>";
print_r($result->parent->distinct()->toArray());
echo "<br><br>";
print_r($result->delete([function ($family) { return $family != "gandomi"; }])->toArray());


/* Output:
Array (
   [0] => Array (
      [family] => Gonjishke
      [parent] => george
   )
   [1] => Array (
      [family] => val john
      [parent] => george
   )
   [2] => Array (
      [family] => gandomi
      [parent] => sam
   )
)


Array (
   [0] => Array (
      [parent] => george
   )
   [2] => Array (
      [parent] => sam
   )
)


Array (
   [0] => Array (
      [family] => gandomi
      [parent] => sam
   )
) 
*/
```
