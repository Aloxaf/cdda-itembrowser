<?php

class ComponentGroup
{
  private $data;
  public $items;
  public function __construct($data)
  {
    $this->data = $data;
    $this->items = array_map(function($i) { 
        return array( 
          "item"=>Items::get($i[0]), 
          "amount"=>$i[1]>1? "$i[1]x  ": ""
        );
    }, $this->data);
  }
}

