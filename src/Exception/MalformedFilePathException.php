<?php

namespace PhpDruidIngest\Exception;

use RuntimeException;

class MalformedFilePathException extends RuntimeException {

    /**
     * @param string $filePath
     * @param int $code [optional] The Exception code.
     * @param Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     */
    public function __construct($filePath, $code = 0, \Exception $previous = null)
    {
        $errorMessage = 'Malformed file path: "' . $filePath . '".';

        parent::__construct($errorMessage, $code, $previous);
    }
}