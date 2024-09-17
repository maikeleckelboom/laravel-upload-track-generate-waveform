<?php

namespace App\Exceptions;

use Exception;

class ChunkCannotBeStored extends Exception
{
    public function __construct(
        $message =  'Unable to store chunk. Please try again',
        $code = 500,
        Exception $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
