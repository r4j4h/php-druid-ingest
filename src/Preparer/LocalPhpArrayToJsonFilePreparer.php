<?php

namespace PhpDruidIngest\Preparer;


class LocalPhpArrayToJsonFilePreparer extends LocalFilePreparer
{

    public $outputFilename = 'temp_LocalPhpArrayToJsonFilePreparer_file.json';

    /*
     * Prepare a file for ingestion.
     *
     * @param array $data Array of records to write
     * @return string Path of locally prepared file
     */
    public function prepare($data) {

        $data = $this->convertPhpArrayToJson($data);

        return parent::prepare($data);

    }

    protected function convertPhpArrayToJson($zeArray)
    {
        $zeJsonArray = array();

        foreach( $zeArray as $val ) {
            $zeJsonArray[] = $this->encodeFactToJson( $val );
        }

        return $zeJsonArray;
    }

    /**
     * Encode an individual fact.
     *
     * (Also a protected function for testability)
     *
     * @param $fact
     * @return string
     */
    protected function encodeFactToJson($fact)
    {
        return json_encode( $fact );
    }

}
