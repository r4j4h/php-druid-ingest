<?php

namespace PhpDruidIngest\QueryGenerator;

use DruidFamiliar\Interfaces\IDruidQueryGenerator;
use DruidFamiliar\Interfaces\IDruidQueryParameters;
use PhpDruidIngest\QueryParameters\IndexingTaskStatusQueryParameters;
use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;

/**
 * Class IndexingTaskStatusQueryGenerator acts as a no-operation class.
 *
 * @package PhpDruidIngest\QueryGenerator
 */
class IndexingTaskStatusQueryGenerator implements IDruidQueryGenerator
{

    /**
     * @param IndexingTaskStatusQueryParameters $indexTaskParams
     * @return string $output
     */
    public function generateQuery(IDruidQueryParameters $indexTaskParams)
    {
        return '';
    }

}
