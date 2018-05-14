# Piqla v1.1.16

>I found today a project with similar name ([Pinq](https://github.com/TimeToogo/Pinq), and also similar work!!) in github, released about four years ago!!
>
>So I decided to change my project name to "__Piqla__"
>
>___(This message is temperory)___

This version of Piqla just supports "Piqla to array"

## Available methods

```php
function where(callable ...$params);
function select(callable ...$params);
function delete(callable ...$params);
function update(callable ...$params);
function insert(callable ...$params);
function orderBy(boolean $accending, callable ...$params);
function orderAscendingBy(callable ...$params);
function orderDescendingBy(callable ...$params);
function distinct();
function count();
function min();
function max();
function sum(callable ...$params);
function average(callable ...$params);
function limit(int $count, int $offset);
function offset(int $offset);
```

## How to use

Define an array and pass that to new Piqla instance constructor

Callback functions must have one argument at least
This argument (its name doen't matter) is current item in list in loop
After that, other else arguments must match with list members name to pass value of that member instead of that argument and these arguments are not required and you have not to define argument for each member beacause you can access by first argument to all of members and other arguments use for easy access to some members.

Now you can use Piqla deformer functions and call them on returned result again and again and ... (like bellow samples)

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
            "name" => "jack",
            "family" => "landan",
            "age" => 37
        ],
        [
            "name" => "john",
            "family" => "val john",
            "age" => 63
        ]
    ];

$var = new Piqla($var);
```

### Test functions

#### where()

```php
echo "<h3>Where:</h3>";
$var_piqla->where(function ($item, $name, $family, $age) {
    return ($age > 35);
});

// Output: [{name => "jack", family => "gonjishke", age => 45},{name => "john", family => "val john", age => 63}]
```

#### select()

```php
$var_piqla->select(function ($item, $name, $family, $age) {
    return ["fullname" => $family . ", " . $name, "old" => ($age > 40)];
});

// Output: [fullname: "gonjishke, jack", old: true}, {fullname: "gandomi, joe", old => false}, {fullname: "landan, jack" old: false}, {fullname: "val john, john", old: true}]


$var_piqla->select(function ($item, $name, $family, $age) {
    if ($age > 40) return ["fullname" => $family . ", " . $name];
});

// Output: [fullname: "gonjishke, jack", {fullname: "val john, john"}]
```

#### delete()

```php
$var_piqla->delete(function ($item, $name, $family, $age) {
    return $age < 40;
});

// Output: [name: "jack", family: "gonjishke", age: 45}, {name: "john", family: "val john", age: 63}]
```

#### update()

```php
$var_piqla->update(function ($item, $name, $family, $age) {
    if ($age > 40)
        return ["age" => round($age / 2), "old" => true];
    else
        return ["old" => false];
});

// Output: [name: "jack", family: "gonjishke", age: 23, old: true}, {name: "joe", family: "gandomi", age: 32, old: false}, {name: "jack", family: "landan", age: 23, old: false}, {name: "john", family: "val john", age: 32, old: true}]
```

#### insert()

```php
$var_piqla->insert(function () {
    return ["name" => "nicol", "family" => "cadmiom", "old" => true];
});

// Output: [name: "jack", family: "gonjishke", age: 45, old: NULL}, {name: "joe", family: "gandomi", age: 32, old: NULL}, {name: "jack", family: "landan", age: 23, old: NULL}, {name: "john", family: "val john", age: 63, old: NULL}, {name: "nicol", family: "cadmiom", old: true, age: NULL}]
```

#### orderBy()

```php
$var_piqla->orderDescendingBy(function ($item, $name, $family, $age) {
    return [$age > 35, -$age];
});

// Output: [name: "jack", family: "gonjishke", age: 45}, {name: "john", family: "val john", age: 63}, {name: "jack", family: "landan", age: 23}, {name: "joe", family: "gandomi", age: 32}]
```

#### distinct()

```php
$var_piqla->name->distinct();

// Output: [name: "jack"}, {name: "joe"}, {name: "john"}]
```

#### count()

```php
$var_piqla->count();

// Output: 4
```

#### min()

```php
$var_piqla->min(function ($item, $age) {
    if ($item["name"] == "jack") {
        return $age;
    }
}, function ($item) {
    if ($item["name"] != "jack") {
        return strlen($item["name"]);
    }
});

// Output: [name: "jack", family: "landan", age: 23}, {name: "joe", family: "gandomi", age: 32}]
```

#### max()

```php
$var_piqla->max(function ($item, $age) {
    if ($item["name"] == "jack") {
        return $age;
    }
}, function ($item) {
    if ($item["name"] != "jack") {
        return strlen($item["name"]);
    }
});

// Output: [name: "jack", family: "gonjishke", age: 45}, {name: "john", family: "val john", age: 63}]
```

#### sum()

```php
$var_piqla->sum(function ($item, $age) {
    if ($item["name"] == "jack") {
        return $age;
    }
}, function ($item) {
    if ($item["name"] != "jack") {
        return strlen($item["name"]);
    }
})->sum();

// Output: [75]
```

#### average()

```php
$var_piqla->average(function ($item, $age) {
    if ($item["name"] == "jack") {
        return $age;
    }
}, function ($item) {
    if ($item["name"] != "jack") {
        return strlen($item["name"]);
    }
});

// Output: [34, 3.5]
```

#### limit()

```php
$var_piqla->limit(2, 1);

// Output: [name: "joe", family: "gandomi", age: 32}, {name: "jack", family: "landan", age: 23}]
```

#### offset()

```php
$var_piqla->offset(2);

// Output: [name: "jack", family: "landan", age: 23}, {name: "john", family: "val john", age: 63}]
```
