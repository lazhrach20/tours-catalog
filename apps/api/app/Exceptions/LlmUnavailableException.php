<?php

namespace App\Exceptions;

use Exception;

class LlmUnavailableException extends Exception
{
    public function __construct(
        string $message = 'LLM service is unavailable.',
        public ?string $codeKey = 'LLM_UNAVAILABLE',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
