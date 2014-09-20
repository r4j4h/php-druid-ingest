<?php

namespace PhpDruidIngest\Interfaces;

interface IIndexBodyGenerator
{
    /**
     * Generate an indexing task POST body
     *
     * @param string $pathOfPreparedData
     * @param ? $dimensionData
     * @return mixed
     */
    public function generateIndex($pathOfPreparedData, $dimensionData);
}