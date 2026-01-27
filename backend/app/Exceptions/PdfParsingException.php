<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class PdfParsingException extends Exception
{
    public function __construct(
        string $message = 'Failed to parse PDF file',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
