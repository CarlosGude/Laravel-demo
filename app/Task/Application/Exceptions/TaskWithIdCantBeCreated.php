<?php

namespace App\Task\Application\Exceptions;

class TaskWithIdCantBeCreated extends \Exception
{
    public function __construct()
    {
        return parent::__construct('Task with an id is not allowed');
    }
}
