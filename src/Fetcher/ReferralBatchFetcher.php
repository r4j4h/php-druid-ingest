<?php

namespace PhpDruidIngest\Fetcher;

use DateTime;
use DruidFamiliar\DruidTime;
use DruidFamiliar\Interval;
use RuntimeException;
use mysqli;
use PhpDruidIngest\Abstracts\BaseFetcher;
use PhpDruidIngest\Interfaces\IFetcher;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReferralBatchFetcher fetches Referral Data from an app MySQL database.
 *
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
     * @var OutputInterface
     */
    protected $output;


    public function __construct() {
        $this->output = new NullOutput();
    }



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

        if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
            $this->output->writeln("Connected to MySQL Database at " . $this->host . " as user " . $this->user . " using db " . $this->db);
        } else {
            $this->output->writeln("Connected to MySQL Database at " . $this->host);
        }


        $preparedQuery = $this->prepareQuery( $this->contactsQuery, $this->intervals->getStart(), $this->intervals->getEnd() );
//        $preparedQuery = $this->prepareQuery( $this->physicianQuery, $this->timeWindowStart, $this->timeWindowEnd );

        if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
            $this->output->writeln("Prepared query:\n\n" . $preparedQuery . "\n\n");
        }


        // Select queries return a resultset
        if ($result = $mysqli->query( $preparedQuery, MYSQLI_USE_RESULT )) {


            if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
                $this->output->writeln("Iterating mysql result set...");
            }

            while ($row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $rows[] = $this->processRow($row);
            }


            if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
                $this->output->writeln("Finished iterating mysql result set.");
            }

            /* free result set */
            $result->close();

        } else {
            throw new \Exception('Query did not return result set.');
        }

        $mysqli->close();

        if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
            $this->output->writeln("Successfully closed MySQL Connection.");
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

        $startTime = new \DateTime( $start );
        $endTime = new \DateTime( $end );

        $formattedStartTime = $startTime->format(DATE_ISO8601);
        $formattedEndTime = $endTime->format(DATE_ISO8601);

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
        $timeForDruid = new DruidTime( new DateTime( $row['date'] ) );
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
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

}