<?php

namespace PhpDruidIngest;

interface IIndexer {

    /**
     * Generate the JSON body of a Druid Indexing task ready for POSTing.
     *
     * @return String
     */
    public function generateIndex();


    public function setIngestionFilePath($path);
    public function getIngestionFilePath();

    public function setDimensions(Array $dims);

    /**
     * @return Array
     */
    public function getDimensions();

    public function setDataSource($dataSource);
    public function getDataSource();

}
