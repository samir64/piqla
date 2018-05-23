<?php
class Piqla
{
    private $data = array();

    public function __debugInfo()
    {
        return $this->data;
    }

    public function __clone()
    {
        return new Piqla($this->data);
    }

    private function _where($function)
    {
        $result = array();

        foreach ($this->data as $item) {
            if (call_user_func($function, $item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function _select($function)
    {
        $result = array();

        foreach ($this->data as $item) {
            $res = call_user_func($function, $item);
            if ($res) {
                $result[] = $res;
            }
        }

        return $result;
    }

    private function _delete($function)
    {
        $result = array();

        foreach ($this->data as $item) {
            if (!call_user_func($function, $item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function _update($function)
    {
        $result = array();

        foreach ($this->data as $item) {
            $row = $item;
            $res = call_user_func($function, $item);
            if ($res) {
                foreach ($res as $key => $value) {
                    $row[$key] = $value;
                }
            }
            $result[] = $row;
        }

        return $result;
    }

    private function _insert($function)
    {
        $result = array();
        $res = $function();
        if (!is_array($res)) {
            $res = null;
        }
        $resFields = is_array($res) ? array_keys($res) : [];
        $memFields = [];

        foreach ($this->data as $item) {
            $newRow = $item;

            if (count($memFields) == 0) {
                $memFields = array_keys($item);
            }

            if (is_array($res)) {
                foreach ($resFields as $key) {
                    if (!array_key_exists($key, $item)) {
                        $newRow[$key] = null;
                    }
                }
            }

            $result[] = $newRow;
        }

        if (is_array($res)) {
            foreach ($memFields as $key) {
                if (!array_key_exists($key, $res)) {
                    $res[$key] = null;
                }
            }

            $result = array_merge($result, [$res]);
        }

        return $result;
    }

    private function _orderBy($ascending, $function)
    {
        $check = function ($item1, $item2) use ($ascending, $function) {
            $value1 = call_user_func($function, $item1);
            $value2 = call_user_func($function, $item2);

            return (($value1 == $value2) ? 0 : ((($value1 > $value2) === $ascending) * 2 - 1));
        };

        $result = $this->data;

        uasort($result, $check);

        return $result;
    }

    private function _join($list, $function, $selectFunction)
    {
        $result = array();

        foreach ($this->data as $leftItem) {
            foreach ($list as $rightItem) {
                $res = call_user_func($function, $leftItem, $rightItem);
                if ($res) {
                    $result[] = call_user_func($selectFunction, $leftItem, $rightItem);
                }
            }
        }

        return $result;
    }

    private function _group($heads, $function)
    {
        $result = array();

        foreach ($this->data as $item) {
            $res = call_user_func($heads, $item);
            if (!array_key_exists($res, $result)) {
                $result[$res] = array();
            }
            $result[$res][] = call_user_func($function, $item);
        }

        foreach ($result as $head => $items) {
            $result[$head] = new Piqla($items);
        }

        return $result;
    }

    private function _min_max($min, $return_item, $function)
    {
        $result = null;
        $check = null;

        foreach ($this->data as $item) {
            $res = call_user_func($function, $item);

            if (!is_null($res) && (is_null($check) || (($res < $check) == $min))) {
                $check = $res;
                $result = $item;
            }
        }
        return $return_item ? $result : $check;
    }

    private function _sum($function)
    {
        $result = 0;

        foreach ($this->data as $item) {
            $res = call_user_func($function, $item);
            $result += (is_numeric($res) ? $res : 0);
        }
        return $result;
    }

    private function _average($function)
    {
        $result = 0;
        $count = 0;

        foreach ($this->data as $item) {
            $res = call_user_func($function, $item);
            if (is_numeric($res)) {
                $result += $res;
                $count++;
            }
        }
        return ($result / $count);
    }

    public function __construct(array $input = [])
    {
        $this->data = $input;
    }

    public function __get($name)
    {
        $result = array();

        foreach ($this->data as $item) {
            if (array_key_exists($name, $item)) {
                $result[] = [$name => $item[$name]];
            }
        }

        return new Piqla($result);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {

    }

    /**
     * @param callable $func
     * @return Piqla
     */
    public function where(callable $func)
    {
        $result = array();

        if (is_null($func)) {
            $func = function ($item) {
                return $item;
            };
        }

        $result = $this->_where($func);

        return new Piqla($result);
    }

    /**
     * @param callable $func
     * @return Piqla
     */
    public function select(callable $func)
    {
        $result = array();

        if (is_null($func)) {
            $func = function ($item) {
                return $item;
            };
        }

        $result = $this->_select($func);

        return new Piqla($result);
    }

    /**
     * @param callable $func
     * @return Piqla
     */
    public function delete(callable $func)
    {
        $result = array();

        if (is_null($func)) {
            $func = function ($item) {
                return true;
            };
        }

        $result = $this->_delete($func);

        return new Piqla($result);
    }

    /**
     * @param callable $func
     * @return Piqla
     */
    public function update(callable $func)
    {
        $result = array();

        $result = $this->_update($func);

        return new Piqla($result);
    }

    /**
     * @param callable $func
     * @return Piqla
     */
    public function insert(callable $func)
    {
        $result = $this->_insert($func);

        return new Piqla($result);
    }

    /**
     * @param boolean $accending
     * @param callable $func
     * @return Piqla
     */
    public function orderBy($ascending, callable $func)
    {
        $result = $this->_orderBy($ascending === true, $func);

        return new Piqla($result);
    }

    /**
     * @param callable $func
     * @return Piqla
     */
    public function orderAscendingBy(callable $func)
    {
        return $this->orderBy(true, $func);
    }

    /**
     * @param callable $func
     * @return Piqla
     */
    public function orderDescendingBy(callable $func)
    {
        return $this->orderBy(false, $func);
    }

    /**
     * @param array $list
     * @param callable $where
     * @return Piqla
     */
    public function join(array $list, callable $where, callable $select = null)
    {
        $result = array();

        if (is_null($where)) {
            $where = function ($leftItem, $rightItem) {
                return true;
            };
        }

        if (is_null($select)) {
            $select = function ($left, $right) {
                return [$left, $right];
            };
        }

        $result = $this->_join($list, $where, $select);

        return new Piqla($result);
    }

    /**
     * @param callable $heads
     * @param callable $select
     * @return Piqla[]
     */
    public function group(callable $heads, callable $select)
    {
        $result = array();

        if (is_null($select)) {
            $select = function ($item) {
                return $item;
            };
        }

        $result = $this->_group($heads, $select);

        return $result;
    }

    /**
     * @return Piqla
     */
    public function distinct()
    {
        $result = array_unique($this->data, SORT_REGULAR);

        return new Piqla($result);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @param callable $func
     * @return mixed
     */
    public function min(callable $func, $return_item = true)
    {
        $result = null;

        if (is_null($func)) {
            $func = function ($item) {
                return $item;
            };
        }

        $result = $this->_min_max(true, $return_item, $func);

        return $result;
    }

    /**
     * @param callable $func
     * @return mixed
     */
    public function max(callable $func, $return_item = true)
    {
        $result = [];

        if (is_null($func)) {
            $func = function ($item) {
                return $item;
            };
        }

        $result = $this->_min_max(false, $return_item, $func);

        return $result;
    }

    /**
     * @param callable $func
     * @return number
     */
    public function sum(callable $func)
    {
        $result = 0;

        if (is_null($func)) {
            $func = function ($item) {
                return $item;
            };
        }

        $result = $this->_sum($func);

        return $result;
    }

    /**
     * @param callable $func
     * @return number
     */
    public function average(callable $func)
    {
        $result = 0;

        if (is_null($func)) {
            $func = function ($item) {
                return $item;
            };
        }

        $result = $this->_average($func);

        return $result;
    }

    /**
     * @param integer $count
     * @param integer $offset
     * @return Piqla
     */
    public function limit($count, $offset = 0)
    {
        if (is_int($count) && is_int($offset) && ($count >= 0)) {
            return new Piqla(array_slice($this->data, $offset, $count));
        } else {
            return new Piqla();
        }
    }

    /**
     * @param integer $offset
     * @return Piqla
     */
    public function offset($offset)
    {
        if (is_int($offset)) {
            return new Piqla(array_slice($this->data, $offset));
        } else {
            return new Piqla();
        }
    }

    /**
     * @param string $json
     * @return void
     */
    public function fromJsonString($json)
    {
        $this->data = json_decode($json);
    }

    /**
     * @return string
     */
    public function toJsonString()
    {
        return json_encode($this->data);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return object[]
     */
    public function toObject()
    {
        $result = [];

        foreach ($this->data as $item) {
            $result[] = (object)$item;
        }

        return $result;
    }
}
