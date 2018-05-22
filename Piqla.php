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

    private function getVariables($function)
    {
        $args = array_slice((new ReflectionFunction($function))->getParameters(), 1);
        $variables = array();
        foreach ($args as $arg) {
            $variables[] = $arg->name;
        }

        return $variables;
    }

    private function getValues($array, $variables)
    {
        $result = [];

        foreach ($variables as $key) {
            $result[] = $array[$key];
        }

        return $result;
    }

    private function _where($function, $variables)
    {
        $result = array();

        foreach ($this->data as $item) {
            if (call_user_func_array($function, array_merge([$item], $this->getValues($item, $variables)))) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function _select($function, $variables)
    {
        $result = array();

        foreach ($this->data as $item) {
            $res = call_user_func_array($function, array_merge([$item], $this->getValues($item, $variables)));
            if ($res) {
                $result[] = $res;
            }
        }

        return $result;
    }

    private function _delete($function, $variables)
    {
        $result = array();

        foreach ($this->data as $item) {
            if (!call_user_func_array($function, array_merge([$item], $this->getValues($item, $variables)))) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function _update($function, $variables)
    {
        $result = array();

        foreach ($this->data as $item) {
            $row = $item;
            $res = call_user_func_array($function, array_merge([$item], $this->getValues($item, $variables)));
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

    private function _orderBy($function, $variables, $ascending)
    {
        $check = function ($item1, $item2) use ($function, $variables, $ascending) {
            $value1 = call_user_func_array($function, array_merge([$item1], $this->getValues($item1, $variables)));
            $value2 = call_user_func_array($function, array_merge([$item2], $this->getValues($item2, $variables)));

            return (($value1 == $value2) ? 0 : ((($value1 > $value2) === $ascending) * 2 - 1));
        };

        $result = $this->data;

        uasort($result, $check);

        return $result;
    }

    private function _min_max($min, $function, $variables)
    {
        $result = null;
        $check = null;

        foreach ($this->data as $item) {
            $res = call_user_func_array($function, array_merge([$item], $this->getValues($item, $variables)));

            if (!is_null($res) && (is_null($check) || (($res < $check) == $min))) {
                $check = $res;
                $result = $item;
            }
        }
        return [$result];
    }

    private function _sum($function, $variables)
    {
        $result = 0;

        foreach ($this->data as $item) {
            $res = call_user_func_array($function, array_merge([$item], $this->getValues($item, $variables)));
            $result += (is_numeric($res) ? $res : 0);
        }
        return [$result];
    }

    private function _average($function, $variables)
    {
        $result = 0;
        $count = 0;

        foreach ($this->data as $item) {
            $res = call_user_func_array($function, array_merge([$item], $this->getValues($item, $variables)));
            if (is_numeric($res)) {
                $result += $res;
                $count++;
            }
        }
        return [$result / $count];
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
     * @param callable ...$funcs
     * @return Piqla
     */
    public function where(callable ...$funcs)
    {
        $result = array();

        if (count($funcs) == 0) {
            $funcs = [function ($item) {
                return $item;
            }];
        }

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_where($callback, $variables));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return Piqla
     */
    public function select(callable ...$funcs)
    {
        $result = array();

        if (count($funcs) == 0) {
            $funcs = [function ($item) {
                return $item;
            }];
        }

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_select($callback, $variables));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return Piqla
     */
    public function delete(callable ...$funcs)
    {
        $result = array();

        if (count($funcs) == 0) {
            $funcs = [function ($item) {
                return true;
            }];
        }

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_delete($callback, $variables));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return Piqla
     */
    public function update(callable ...$funcs)
    {
        $result = array();

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_update($callback, $variables));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return Piqla
     */
    public function insert(callable ...$funcs)
    {
        $result = array();

        foreach ($funcs as $callback) {
            // $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_insert($callback));
        }

        return new Piqla($result);
    }

    /**
     * @param boolean $accending
     * @param callable ...$funcs
     * @return Piqla
     */
    public function orderBy($ascending, callable ...$funcs)
    {
        $result = array();

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_orderBy($callback, $variables, $ascending === true));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return Piqla
     */
    public function orderAscendingBy(callable ...$funcs)
    {
        return $this->orderBy(true, ...$params);
    }

    /**
     * @param callable ...$funcs
     * @return Piqla
     */
    public function orderDescendingBy(callable ...$funcs)
    {
        return $this->orderBy(false, ...$funcs);
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
     * @param callable ...$funcs
     * @return Piqla
     */
    public function min(callable ...$funcs)
    {
        $result = [];

        if (count($funcs) == 0) {
            $funcs = [function ($item) {
                return $item;
            }];
        }

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_min_max(true, $callback, $variables));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return Piqla
     */
    public function max(callable ...$funcs)
    {
        $result = [];

        if (count($funcs) == 0) {
            $funcs = [function ($item) {
                return $item;
            }];
        }

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_min_max(false, $callback, $variables));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return number
     */
    public function sum(callable ...$funcs)
    {
        $result = [];

        if (count($funcs) == 0) {
            $funcs = [function ($item) {
                return $item;
            }];
        }

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_sum($callback, $variables));
        }

        return new Piqla($result);
    }

    /**
     * @param callable ...$funcs
     * @return number
     */
    public function average(callable ...$funcs)
    {
        $result = [];

        if (count($funcs) == 0) {
            $funcs = [function ($item) {
                return $item;
            }];
        }

        foreach ($funcs as $callback) {
            $variables = $this->getVariables($callback);
            $result = array_merge($result, $this->_average($callback, $variables));
        }

        return new Piqla($result);
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
