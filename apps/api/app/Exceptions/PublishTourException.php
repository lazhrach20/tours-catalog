<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class PublishTourException extends Exception
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        string $message,
        public array $errors,
    ) {
        parent::__construct($message);
    }

    public function toMessageBag(): MessageBag
    {
        return new MessageBag($this->errors);
    }
}
