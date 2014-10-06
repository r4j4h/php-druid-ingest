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
 * Class ReferralBatchFetcher fetches Referral Data from an app MySQL database.
 * It has been superceded by MySQLBatchFetcher and this class will be removed soon. It is a job for a consumer, not
 * this library itself.
 *
 * @deprecated
 * @package PhpDruidIngest\Fetcher
 */
class ReferralBatchFetcher extends BaseFetcher implements IFetcher
{

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

    protected $contactsQuery = <<<QUERY

QUERY;


    protected $physicianQuery = <<<QUERY

QUERY;


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
        if ( $this->host == '' || $this->user == '' || !$this->pass || !$this->db ) {
            throw new RuntimeException('Database configuration not configured.');
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


        $preparedQuery = $this->prepareQuery( $this->contactsQuery, $this->intervals->getStart(), $this->intervals->getEnd() );
//        $preparedQuery = $this->prepareQuery( $this->physicianQuery, $this->timeWindowStart, $this->timeWindowEnd );

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
        // todo could fail here ..needs exception!
        $timeForDruid = new DruidTime( $row['date'] );
        $row['date'] = $timeForDruid->formatTimeForDruid();

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