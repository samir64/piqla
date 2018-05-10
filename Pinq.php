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
      if (is_array($param)) {
         foreach ($param as $variablesString => $function) {
            if (is_callable($function)) {
               $variables = array_map("trim", explode(",", $variablesString));
               $result = array();
               array_map(function ($element) use ($function, $variables, &$result) {$res = call_user_func_array($function, $this->getValues($element, $variables));if ($res) {$result[] = $res;return $res;} else {
                  return false;
               }
               }, $this->members);

               return new Pinq($result);
            }
         }
      }
   }
   
   public function distinct()
   {
      $result = array();
      
      foreach ($this->members as $item) {
         
      }
      
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

