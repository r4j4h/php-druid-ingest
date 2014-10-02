<?php

namespace PhpDruidIngest\Exception;

class UnexpectedTypeException extends \InvalidArgumentException {

    /**
     * @param mixed $actual The thing that was given. For example: $myVar
     * @param string $expectedType What it was expected to be. For example: 'array'
     * @param int $code [optional] The Exception code.
     * @param \Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     */
    public function __construct($actual, $expectedType, $code = 0, \Exception $previous = null)
    {
        $actualType = (string)( $actual );
        $errorMessage = 'Expected argument to be of type "' . $expectedType . '", but encountered "' . $actualType . '".';

        parent::__construct($errorMessage, $code, $previous);
    }
}