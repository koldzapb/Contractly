<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class UnsupportedFileTypeException extends Exception
{
    public function __construct(
        public readonly string $extension,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $message = "Unsupported file type: .{$extension}";
        parent::__construct($message, $code, $previous);
    }
}
