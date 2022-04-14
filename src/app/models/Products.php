<?php

use Phalcon\Mvc\Model;

class Products extends Model
{
    public $product_id;
    public $product_name;
    public $product_description;
    public $product_tags;
    public $product_stock;
    public $product_price;
}