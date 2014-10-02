<?php

namespace PhpDruidIngest\QueryGenerator;

use DruidFamiliar\Interfaces\IDruidQueryGenerator;
use DruidFamiliar\Interfaces\IDruidQueryParameters;
use PhpDruidIngest\QueryParameters\IndexingTaskStatusQueryParameters;
use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;

/**
 * Class IndexingTaskStatusQueryGenerator acts as a no-operation class for checking the status of tasks.
 *
 * These operations do not need any BODY or query parameters, as they are built via url arguments.
 *
 * @package PhpDruidIngest\QueryGenerator
 */
class IndexingTaskStatusQueryGenerator implements IDruidQueryGenerator
{

    /**
     * @param IndexingTaskStatusQueryParameters $indexTaskParams
     * @return string Query payload in JSON
     */
    public function generateQuery(IDruidQueryParameters $indexTaskParams)
    {
        $indexTaskParams->validate();
        return '';
    }

}
