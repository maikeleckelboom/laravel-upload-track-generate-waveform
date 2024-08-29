<?php

namespace App\Exceptions;

use Exception;

class ChunkCountMismatch extends Exception
{
    public function __construct(
        $message =  'The number of chunks received does not match the total number of chunks',
        $code = 400,
        Exception $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
