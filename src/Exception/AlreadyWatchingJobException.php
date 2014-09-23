<?php
/**
 * Created by PhpStorm.
 * User: jhegman
 * Date: 9/22/14
 * Time: 12:46 PM
 */

namespace PhpDruidIngest\Exception;

use Exception;

class AlreadyWatchingJobException extends Exception {

    /**
     * @param string $currentlyWatchedJobId The Job Id currently watching
     * @param null $newJobId [optional] The Job Id asked to watch
     * @param \Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     */
    public function __construct($currentlyWatchedJobId, $newJobId = null, \Exception $previous = null)
    {
        $errorMessage = "Already watching Druid Job Id ($currentlyWatchedJobId).";

        if ( $newJobId ) {
            $errorMessage .= " Unable to watch Druid Job Id ($newJobId).";
        }

        parent::__construct($errorMessage, 0, $previous);
    }
} 