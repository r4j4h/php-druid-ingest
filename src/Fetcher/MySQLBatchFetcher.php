<?php

namespace PhpDruidIngest\Fetcher;

use DateTime;
use DruidFamiliar\DruidTime;
use DruidFamiliar\Interval;
use RuntimeException;
use mysqli;
use PhpDruidIngest\Abstracts\BaseFetcher;
use PhpDruidIngest\Interfaces\IFetcher;
use Psr\Log\LoggerInterface;

/**
 * Class MySQLBatchFetcher fetches data from an app MySQL database using a query.
 *
 * @package PhpDruidIngest\Fetcher
 */
class MySQLBatchFetcher extends BaseFetcher implements IFetcher
{

    /**
     * MySQL Query Template that is expecting to receive a start and end date as parameters via prepareQuery.
     *
     * The parameters are inserted via string replacement:
     * {STARTDATE} and {ENDDATE}
     *
     * An example template:
     * "SELECT id FROM things WHERE date BETWEEN '{STARTDATE}' AND '{ENDDATE}';"
     *
     * @var string
     */
    protected $query;


    /**
     * @var Interval
     */
    protected $intervals = null;

    protected $host = '';
    protected $user = '';
    protected $pass = '';
    protected $db = '';

    /**
     * @var LoggerInterface
     */
    protected $output;


    /**
     * Set the MySQL DB credentials for fetching.
     *
     * @param $host
     * @param $user
     * @param $pass
     * @param $db
     */
    public function setMySqlCredentials($host, $user, $pass, $db) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
    }

    /**
     * Set the time window for fetching.
     *
     * @param string $start ISO DateTime for start of ingestion window
     * @param string $end ISO DateTime for end of ingestion window
     * @return string
     */
    public function setTimeWindow($start, $end) {
        $this->setIntervals($start, $end);
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
     * Fetch data.
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function fetch()
    {
        if ( !$this->intervals ) {
            throw new RuntimeException('Fetch ingestion interval not configured.');
        }
        if ( $this->host == '' || $this->user == '' || !$this->db ) {
            throw new RuntimeException('Database configuration not configured.');
        }
        if ( !$this->pass ) {
            $this->pass = '';
        }


        $mysqli = $this->getMysqli($this->host, $this->user, $this->pass, $this->db);
        $rows = array();

        // Check connection
        if ($mysqli->connect_errno) {
            throw new \Exception( sprintf("Connect failed: %s\n", $mysqli->connect_error) );
        }

        if ($this->output) {
            $this->output->info("Connected to MySQL Database at " . $this->host);
            $this->output->debug("Connected to MySQL Database at " . $this->host . " as user " . $this->user . " using db " . $this->db);
        }


        $preparedQuery = $this->prepareQuery( $this->query, $this->intervals->getStart(), $this->intervals->getEnd() );

        if ($this->output) {
            $this->output->debug("Prepared query:\n\n" . $preparedQuery . "\n\n");
        }


        // Select queries return a resultset
        if ($result = $mysqli->query( $preparedQuery, MYSQLI_USE_RESULT )) {


            if ($this->output) {
                $this->output->info("Iterating mysql result set...");
            }

            while ($row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $rows[] = $this->processRow($row);
            }


            if ($this->output) {
                $this->output->info("Finished iterating mysql result set.");
            }

            /* free result set */
            $result->close();

        } else {
            throw new \Exception('Query did not return result set.');
        }

        $mysqli->close();

        if ($this->output) {
            $this->output->info("Successfully closed MySQL Connection.");
        }

        return $rows;

    }


    /**
     * Bind the start and end ingestion date windows to a query.
     *
     * @param String $query Query with {STARTDATE} and {ENDDATE} for value substituion
     * @param String $start ISO Date Time string
     * @param String $end ISO Date Time string
     * @return String Prepared query string
     */
    public function prepareQuery($query, DruidTime $start, DruidTime $end)
    {

        $startTime = new DruidTime( $start );
        $endTime = new DruidTime( $end );

        $formattedStartTime = $startTime->formatTimeForDruid();
        $formattedEndTime = $endTime->formatTimeForDruid();

        $preparedQuery = $query;
        $preparedQuery = str_replace( '{STARTDATE}', $formattedStartTime, $preparedQuery );
        $preparedQuery = str_replace( '{ENDDATE}', $formattedEndTime, $preparedQuery );

        return $preparedQuery;

    }

    /**
     * Process an item in the fetched items array
     *
     * @param $row
     * @return mixed
     */
    protected function processRow($row)
    {
        return $row;
    }

    /**
     * @param $host
     * @param $user
     * @param $pass
     * @param $db
     * @return mysqli
     */
    protected function getMysqli($host, $user, $pass, $db)
    {
        $mysqli = new mysqli($host, $user, $pass, $db);
        return $mysqli;
    }

    /**
     * @return LoggerInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param LoggerInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

}