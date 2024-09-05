<?php

namespace App\Exceptions;

use Exception;

class AudioStreamNotFound extends Exception
{
    public function __construct(
        $fileName,
        $disk,
        $code = 404,
        Exception $previous = null
    )
    {
        $message = "No audio stream found in file '{$fileName}' on disk '{$disk}'";
        parent::__construct($message, $code, $previous);
    }
}
