<?php

namespace PhpDruidIngest\Exception;

use RuntimeException;

class UnableToDeleteFileException extends RuntimeException {

    /**
     * @param string $filePath
     * @param int $code [optional] The Exception code.
     * @param \Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     */
    public function __construct($filePath, $code = 0, \Exception $previous = null)
    {
        $errorMessage = 'Unable to delete file: "' . $filePath . '".';

        parent::__construct($errorMessage, $code, $previous);
    }
}