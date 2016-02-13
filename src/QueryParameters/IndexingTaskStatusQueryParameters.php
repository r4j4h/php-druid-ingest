<?php

namespace PhpDruidIngest\QueryParameters;

use DruidFamiliar\Abstracts\AbstractTaskParameters;
use DruidFamiliar\Exception\MissingParametersException;
use DruidFamiliar\Interfaces\IDruidQueryParameters;

class IndexingTaskStatusQueryParameters extends AbstractTaskParameters implements IDruidQueryParameters
{

    protected $taskId;

    /**
     * @throws MissingParametersException
     */
    public function validate()
    {
        if ( !$this->taskId ) {
            throw new \Exception('No task id set.');
        }
    }

    /**
     * @return mixed
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param mixed $taskId
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
    }

}