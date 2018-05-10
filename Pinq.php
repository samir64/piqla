<?php
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

   public function where(array $params)
   {
      $result = array();

      if (is_callable($params)) {
         $params = [$params];
      }

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

   public function select(array $params)
   {
      $result = array();

      if (is_callable($params)) {
         $params = [$params];
      }

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
   
   public function delete($params = [])
   {
      $result = array();

      if (is_callable($params)) {
         $params = [$params];
      }

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
   
   public function distinct()
   {
      $result = array_unique($this->members, SORT_REGULAR);

      return new Pinq($result);
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

