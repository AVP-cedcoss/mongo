<?php

use Phalcon\Mvc\Model;

class Permission extends Model
{
    public $permission_id;
    public $permission_role;
    public $permission_controller;
    public $permission_action;
}
