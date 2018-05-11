<?php
namespace Pinq {
   function from(array $input)
   {
      return new \Pinq($input);
   }
}

namespace {
   class Pinq
   {
      private $members = array();

      private function getVariables($function)
      {
         $args = (new ReflectionFunction($function))->getParameters();
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
         
         foreach ($this->members as $item) {
            if (call_user_func_array($function, $this->getValues($item, $variables))) {
               $result[] = $item;
            }
         }
         
         return $result;
      }

      private function _select($function, $variables)
      {
         $result = array();
         
         foreach ($this->members as $item) {
            $res = call_user_func_array($function, $this->getValues($item, $variables));
            if ($res) {
               $result[] = $res;
            }
         }
         
         return $result;
      }

      private function _delete($function, $variables)
      {
         $result = array();
         
         foreach ($this->members as $item) {
            if (!call_user_func_array($function, $this->getValues($item, $variables))) {
               $result[] = $item;
            }
         }
         
         return $result;
      }

      private function _update($function, $variables)
      {
         $result = array();
         
         foreach ($this->members as $item) {
            $res = call_user_func_array($function, $this->getValues($item, $variables));
            if ($res) {
               foreach ($item as $key => $value) {
                  if (array_key_exists($key, $res)) {
                     $result[$key] = $res[$key];
                  } else {
                     $result[$key] = $value;
                  }
               }
            } else {
               $result[] = $item;
            }
         }
         
         return $result;
      }

      private function _insert($function, $variables)
      {
         $result = array();
         $res = $function();
         if (!is_array($res)) {
            $res = null;
         }
         $resFields = is_array($res) ? array_keys($res) : [];
         $memFields = [];
         
         foreach ($this->members as $item) {
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

      public function __construct(array $input = [])
      {
         $this->members = $input;
      }

      public function __get($name)
      {
         $result = array();

         foreach ($this->members as $item) {
            if (array_key_exists($name, $item)) {
               $result[] = [$name => $item[$name]];
            }
         }

         return new Pinq($result);
      }

      public function where(callable ...$params)
      {
         $result = array();

         if (is_array($params)) {
            foreach ($params as $callback) {
               if (is_callable($callback)) {
                  $variables = $this->getVariables($callback);
                  $result = array_merge($result, $this->_where($callback, $variables));
               }
            }
         }

         return new Pinq($result);
      }

      public function select(callable ...$params)
      {
         $result = array();
         
         if (is_array($params)) {
            foreach ($params as $callback) {
               if (is_callable($callback)) {
                  $variables = $this->getVariables($callback);
                  $result = array_merge($result, $this->_select($callback, $variables));
               }
            }
         }
         
         return new Pinq($result);
      }
      
      public function delete(callable ...$params)
      {
         $result = array();

         if (is_array($params)) {
            foreach ($params as $callback) {
               if (is_callable($callback)) {
                  $variables = $this->getVariables($callback);
                  $result = array_merge($result, $this->_delete($callback, $variables));
               }
            }
         }
         
         return new Pinq($result);
      }
      
      public function update(callable ...$params)
      {
         $result = array();

         if (is_array($params)) {
            foreach ($params as $callback) {
               if (is_callable($callback)) {
                  $variables = $this->getVariables($callback);
                  $result = array_merge($result, $this->_update($callback, $variables));
               }
            }
         }
         
         return new Pinq($result);
      }
      
      public function insert(callable ...$params)
      {
         $result = array();
         
         if (is_array($params)) {
            foreach ($params as $callback) {
               if (is_callable($callback)) {
                  $variables = $this->getVariables($callback);
                  $result = array_merge($result, $this->_insert($callback, $variables));
               }
            }
         }
         
         return new Pinq($result);
      }
      
      public function distinct()
      {
         $result = array_unique($this->members, SORT_REGULAR);

         return new Pinq($result);
      }
      
      public function count()
      {
         return count($this->members);
      }
      
      public function limit($count, $offset = 0)
      {
         if (is_int($count) && is_int($offset) && ($count >= 0)) {
            return new Pinq(array_slice($this->members, $offset, $count));
         } else {
            return new Pinq();
         }
      }
      
      public function offset($offset)
      {
         if (is_int($offset)) {
            return new Pinq(array_slice($this->members, $offset));
         } else {
            return new Pinq();
         }
      }

      public function toArray()
      {
         return $this->members;
      }

      public function toObject()
      {
         return (object) $this->members;
      }
   }
}
