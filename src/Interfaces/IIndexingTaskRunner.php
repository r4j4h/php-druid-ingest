<?php

namespace PhpDruidIngest\Interfaces;

use DruidFamiliar\Interfaces\IDruidQueryExecutor;

interface IIndexingTaskRunner
{

    /**
     * Execute an indexing task, polling the tasks' status until it completes or resolves.
     *
     * TODO Return a promise
     *
     * @param IDruidQueryExecutor $queryExecutor
     * @param $indexingTaskBody
     * @return mixed
     */
    public function index( IDruidQueryExecutor $queryExecutor, $indexingTaskBody );

}