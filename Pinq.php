<?php
class Pinq
{
   private $members = array();

   private function getValues($array, $variables)
   {
      $result = [];

      foreach ($variables as $key) {
         $result[] = $array[$key];
      }

      return $result;
   }

   private function selectArray($function, $variables)
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

   public function select($param)
   {
      $result = array();
      if (is_array($param)) {
         foreach ($param as $callback) {
            if (is_callable($callback)) {
               $args = (new ReflectionFunction($callback))->getParameters();
               $variables = array();
               foreach ($args as $arg) {
                  $variables[] = $arg->name;
               }
               $result = array_merge($result, $this->selectArray($callback, $variables));
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

