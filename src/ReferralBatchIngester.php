<?php

namespace PhpDruidIngest;

use mysqli;

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

    public function ingest() {

        $mysqli = new mysqli($this->host, $this->user, $this->pass, $this->db);

        /* check connection */
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }

        /* Create table doesn't return a resultset */
        if ($mysqli->query("CREATE TEMPORARY TABLE myCity LIKE City") === TRUE) {
            printf("Table myCity successfully created.\n");
        }

        /* Select queries return a resultset */
        if ($result = $mysqli->query("SELECT Name FROM City LIMIT 10")) {
            printf("Select returned %d rows.\n", $result->num_rows);

            /* free result set */
            $result->close();
        }

        /* If we have to retrieve large amount of data we use MYSQLI_USE_RESULT */
        if ($result = $mysqli->query("SELECT * FROM City", MYSQLI_USE_RESULT)) {

            /* Note, that we can't execute any functions which interact with the
               server until result set was closed. All calls will return an
               'out of sync' error */
            if (!$mysqli->query("SET @a:='this will not work'")) {
                printf("Error: %s\n", $mysqli->error);
            }
            $result->close();
        }

        $mysqli->close();


        return 'hey';

    }

}