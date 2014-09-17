<?php

namespace PhpDruidIngest;

class BaseDruidTaskExecutor
{
    public function index( $druidConnection, $indexBody )
    {
        // TODO Use conn and index body to build out request
        // TODO Submit request
        // TODO Monitor request
        // TODO Poll until status changes to FAILURE or SUCCESS
        // TODO Report results

        $success = false;

        return $success;
    }
}