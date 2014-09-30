<?php

namespace PhpDruidIngest\ResponseHandler;

use DruidFamiliar\Interfaces\IDruidQueryResponseHandler;
use Guzzle\Http\Message\Response;
use PhpDruidIngest\QueryResponse\IndexingTaskStatusQueryResponse;

class IndexingTaskStatusResponseHandler implements IDruidQueryResponseHandler
{

    /**
     * Hook function to parse the task status from the response from server.
     *
     * This hook must return the response, whether changed or not, so that the rest of the system can continue with it.
     *
     * @param Response $response
     * @return IndexingTaskStatusQueryResponse|mixed
     * @throws \Exception
     */
    public function handleResponse($response)
    {
        $taskStatus = new IndexingTaskStatusQueryResponse();

        $response = $response->json();


        if ( !isset( $response['status'] ) ) {
            throw new \Exception("Unexpected response"); // TODO Replace with subclassed exception
        }
        $responseStatus = $response['status'];


        if ( !isset( $response['task'] ) ) {
            throw new \Exception("Unexpected response"); // TODO Replace with subclassed exception
        }
        $taskStatus->setTask( $response['task'] );

        if ( !isset( $responseStatus['id'] ) ) {
            throw new \Exception("Unexpected response"); // TODO Replace with subclassed exception
        }
        $taskStatus->setId( $responseStatus['id'] );

        if ( !isset( $responseStatus['status'] ) ) {
            throw new \Exception("Unexpected response"); // TODO Replace with subclassed exception
        }
        $taskStatus->setStatus( $responseStatus['status'] );

        if ( !isset( $responseStatus['duration'] ) ) {
            throw new \Exception("Unexpected response"); // TODO Replace with subclassed exception
        }
        $taskStatus->setDuration( $responseStatus['duration'] );


        return $taskStatus;
    }

}
