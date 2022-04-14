<?php

// namespace models;

use Phalcon\Mvc\Model;

class Settings extends Model
{
    public $setting_id;
    public $title_optimization;
    public $default_price;
    public $default_stock;
    public $default_zipcode;
}