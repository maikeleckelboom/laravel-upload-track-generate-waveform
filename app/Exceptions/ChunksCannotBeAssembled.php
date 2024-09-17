<?php

namespace App\Exceptions;

use Exception;

class ChunksCannotBeAssembled extends Exception
{
    public function __construct(
        $message = 'The chunks could not be assembled',
        $code = 500,
    )
    {
        parent::__construct($message, $code);
    }
}
