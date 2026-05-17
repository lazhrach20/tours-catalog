<?php

namespace App\Exceptions;

use Exception;

class CategoryInUseException extends Exception
{
    public function __construct()
    {
        parent::__construct('Category cannot be deleted because it is linked to tours.');
    }
}
