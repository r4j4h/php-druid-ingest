<?php

namespace PhpDruidIngest;

use mysqli;
date_default_timezone_set('America/Denver');

class ReferralBatchIngester {

    private $host = '';
    private $user = '';
    private $pass = '';
    private $db = '';

    public function setMySqlCredentials($host, $user, $pass, $db) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
    }



    /**
     * Ingest data into druid.
     *
     * @param string $start ISO DateTime for start of ingestion window
     * @param string $end ISO DateTime for end of ingestion window
     * @return string
     */
    public function ingest($start = '2000-01-01T00:00:01', $end = '3030-01-01T00:00:01')
    {
        $dataBatch = $this->fetch( $start, $end );

        return "Fetched " . count($dataBatch) . " referrals.";
    }


    public function fetch($start, $end)
    {

        $mysqli = new mysqli($this->host, $this->user, $this->pass, $this->db);

        // Check connection
        if ($mysqli->connect_errno) {
            throw new \Exception( sprintf("Connect failed: %s\n", $mysqli->connect_error) );
        }

        echo "Connected.\n";

        $preparedQuery = $this->prepareQuery( $this->physicianQuery, $start, $end );

//        echo $start . "\n";
//        echo $end . "\n";
//        echo $preparedQuery;
        $rows = array();

        // Select queries return a resultset
        if ($result = $mysqli->query( $preparedQuery, MYSQLI_USE_RESULT )) {


            while ($row = $result->fetch_array())
            {
                $rows[] = $row;
            }

            /* free result set */
            $result->close();

        }

        $mysqli->close();

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
    public function prepareQuery($query, $start, $end)
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

}