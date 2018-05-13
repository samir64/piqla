# Pinq v1.1.16

This version of Pinq just supports "Pinq to array"

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

Define an array and pass that to new Pinq instance constructor

Callback functions (that define to $params arguments) must have least one argument
This argument (it's name doen't matter) is every item in list
After that, other else arguments must match with list members name to pass value of that member instead of that argument and these arguments are not required and you have not to define argument for each member beacause you can access by first argument to all of that's members and other arguments is just to easy access to some members.

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

$var = new Pinq($var);
```

### Test functions

#### where()

```php
echo "<h3>Where:</h3>";
$var_pinq->where(function ($item, $name, $family, $age) {
    return ($age > 35);
});

// Output: [{name => "jack", family => "gonjishke", age => 45},{name => "john", family => "val john", age => 63}]
```

#### select()

```php
$var_pinq->select(function ($item, $name, $family, $age) {
    return ["fullname" => $family . ", " . $name, "old" => ($age > 40)];
});

// Output: [fullname: "gonjishke, jack", old: true}, {fullname: "gandomi, joe", old => false}, {fullname: "landan, jack" old: false}, {fullname: "val john, john", old: true}]


$var_pinq->select(function ($item, $name, $family, $age) {
    if ($age > 40) return ["fullname" => $family . ", " . $name];
});

// Output: [fullname: "gonjishke, jack", {fullname: "val john, john"}]
```

#### delete()

```php
$var_pinq->delete(function ($item, $name, $family, $age) {
    return $age < 40;
});

// Output: [name: "jack", family: "gonjishke", age: 45}, {name: "john", family: "val john", age: 63}]
```

#### update()

```php
$var_pinq->update(function ($item, $name, $family, $age) {
    if ($age > 40)
        return ["age" => round($age / 2), "old" => true];
    else
        return ["old" => false];
});

// Output: [name: "jack", family: "gonjishke", age: 23, old: true}, {name: "joe", family: "gandomi", age: 32, old: false}, {name: "jack", family: "landan", age: 23, old: false}, {name: "john", family: "val john", age: 32, old: true}]
```

#### insert()

```php
$var_pinq->insert(function () {
    return ["name" => "nicol", "family" => "cadmiom", "old" => true];
});

// Output: [name: "jack", family: "gonjishke", age: 45, old: NULL}, {name: "joe", family: "gandomi", age: 32, old: NULL}, {name: "jack", family: "landan", age: 23, old: NULL}, {name: "john", family: "val john", age: 63, old: NULL}, {name: "nicol", family: "cadmiom", old: true, age: NULL}]
```

#### orderBy()

```php
$var_pinq->orderDescendingBy(function ($item, $name, $family, $age) {
    return [$age > 35, -$age];
});

// Output: [name: "jack", family: "gonjishke", age: 45}, {name: "john", family: "val john", age: 63}, {name: "jack", family: "landan", age: 23}, {name: "joe", family: "gandomi", age: 32}]
```

#### distinct()

```php
$var_pinq->name->distinct();

// Output: [name: "jack"}, {name: "joe"}, {name: "john"}]
```

#### count()

```php
$var_pinq->count();

// Output: 4
```

#### min()

```php
$var_pinq->min(function ($item, $age) {
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
$var_pinq->max(function ($item, $age) {
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
$var_pinq->sum(function ($item, $age) {
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
$var_pinq->average(function ($item, $age) {
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
$var_pinq->limit(2, 1);

// Output: [name: "joe", family: "gandomi", age: 32}, {name: "jack", family: "landan", age: 23}]
```

#### offset()

```php
$var_pinq->offset(2);

// Output: [name: "jack", family: "landan", age: 23}, {name: "john", family: "val john", age: 63}]
```
