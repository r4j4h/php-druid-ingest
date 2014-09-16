<?php

namespace PhpDruidIngest;

interface ITransformer {

    /**
     * Transform the data for ingestion.
     *
     * This function must return $input even if not transforming it.
     *
     * @param $input
     * @return mixed $output
     */
    public function transform($input);

}
