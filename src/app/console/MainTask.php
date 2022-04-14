<?php

namespace Console;

// use Phalcon\Cli\Console;
use Phalcon\Cli\Task;

class MainTask extends Task
{
    public function mainAction()
    {
        
    }

    public function createTokenAction($role)
    {
        echo $this->objects->listener->createToken($role);
    }
}