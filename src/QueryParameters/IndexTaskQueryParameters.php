<?php

namespace PhpDruidIngest\QueryParameters;

use DruidFamiliar\Abstracts\AbstractTaskParameters;
use DruidFamiliar\Exception\MissingParametersException;
use DruidFamiliar\Exception\UnexpectedTypeException;
use DruidFamiliar\Interfaces\IDruidQueryParameters;
use DruidFamiliar\Interval;

/**
 * Class IndexTaskQueryParameters represents parameter values for an indexing task for Druid.
 *
 * @package PhpDruidIngest
 */
class IndexTaskQueryParameters extends AbstractTaskParameters implements IDruidQueryParameters
{

    /**
     * Query Type.
     *
     * @var string
     */
    public $queryType = 'index';

    /**
     * @var Interval
     */
    public $intervals = null;


    /**
     * @var string
     */
    public $granularityType = 'uniform';


    /**
     * @var string
     */
    public $granularity = 'DAY';


    /**
     * DataSource Name
     *
     * @var string
     */
    public $dataSource;


    /**
     * Path for ingestion. Keep in mind the coordinator and historical nodes will need to be able to access this!
     *
     * Intended to be set through $this->setFilePath(...).
     *
     * @var string
     */
    public $baseDir;


    /**
     * Filename for ingestion. Keep in mind the coordinator and historical nodes will need to be able to access this!
     *
     * Intended to be set through $this->setFilePath(...).
     *
     * @var string
     */
    public $filePath;


    /**
     * Format of ingestion.
     *
     * @var string
     */
    public $format = 'json';


    /**
     * The data's time dimension key.
     *
     * @var string
     */
    public $timeDimension;


    /**
     * Array of strings representing the data's non-time dimensions' keys.
     *
     * @var array
     */
    public $dimensions;


    /**
     * Array of json encoded strings
     *
     * Intended to be set through $this->setAggregators(...).
     *
     * @var array
     */
    public $aggregators = array();


    public function setFilePath($path) {
        $fileInfo = pathinfo($path);
        $this->baseDir = $fileInfo['dirname'];
        $this->filePath = $fileInfo['basename'];
    }

    /**
     * Configure the aggregators for this request.
     *
     * @param $aggregators Array Associative array of aggregators
     */
    public function setAggregators($aggregatorsArray)
    {
        $this->aggregators = array();

        foreach( $aggregatorsArray as $aggregator)
        {
            $this->aggregators[] = json_encode( $aggregator );
        }

    }

    /**
     * Set the interval boundaries for this query.
     *
     * @param string $intervalStart
     * @param string $intervalEnd
     */
    public function setIntervals($intervalStart = "1970-01-01 01:30:00", $intervalEnd = "3030-01-01 01:30:00")
    {
        $this->intervals = new Interval($intervalStart, $intervalEnd);
    }


    /**
     * @throws MissingParametersException
     */
    public function validate()
    {
        $this->validateForMissingParameters();

        $this->validateForEmptyParameters();

        // Validate types
        if ( !$this->intervals instanceof Interval ) {
            throw new UnexpectedTypeException($this->intervals, "DruidFamiliar\Interval", "intervals property.");
        }
    }


    /**
     * @throws MissingParametersException
     */
    protected function validateForMissingParameters()
    {
        // Validate missing params
        $missingParams = array();

        $requiredParams = array(
            'queryType',
            'dataSource',
            'intervals',
            'granularity',
            'dimensions',
            'aggregators',
        );

        foreach ($requiredParams as $requiredParam) {
            if ( !isset( $this->$requiredParam ) ) {
                $missingParams[] = $requiredParam;
            }
        }

        if ( count($missingParams) > 0 ) {
            throw new \DruidFamiliar\Exception\MissingParametersException($missingParams);
        }
    }

    /**
     * @throws MissingParametersException
     */
    protected function validateForEmptyParameters()
    {
        // Validate empty params
        $emptyParams = array();

        $requiredNonEmptyParams = array(
            'queryType',
            'dataSource'
        );

        foreach ($requiredNonEmptyParams as $requiredNonEmptyParam) {
            if ( !isset( $this->$requiredNonEmptyParam ) ) {
                $emptyParams[] = $requiredNonEmptyParam;
            }
        }

        if ( count($emptyParams) > 0 ) {
            throw new \DruidFamiliar\Exception\MissingParametersException($emptyParams);
        }
    }

}