<?php

namespace PhpDruidIngest;

use mysqli;
date_default_timezone_set('America/Denver');

class ReferralBatchIngester implements IFetcher, ITransformer
{

    protected $timeWindowStart;
    protected $timeWindowEnd;

    protected $host = '';
    protected $user = '';
    protected $pass = '';
    protected $db = '';

    public function setMySqlCredentials($host, $user, $pass, $db) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
    }

    public function setTimeWindow($start, $end) {
        $this->timeWindowStart = $start;
        $this->timeWindowEnd = $end;
    }

    protected $contactsQuery = <<<QUERY
SELECT patientTable.created as `date`,
3 as `group`,
Contact.ContactID as referral_id,
FacilityHasPatient.Fcltyhpnt_FacilityID as facility_id,
patientTable.PatientID as patient_id,
Company.Cmp_CompanyID as company_id,
(
    SELECT IF(COUNT(PTCase.CaseID)>0,1,0) as is_active
    FROM Appointment INNER JOIN PTCase ON Appointment.CaseID = PTCase.CaseID
    WHERE Appointment.AptType = 'IE'
    AND Appointment.`Status` = 'A'
    AND Appointment.PatientID = patientTable.PatientID
    AND PTCase.CaseID = cases.CaseID
    ) as is_active_patient,
(
    SELECT IF(COUNT(PTCase.CaseID)>0,1,0) as is_active
    FROM Appointment INNER JOIN PTCase ON Appointment.CaseID = PTCase.CaseID
    WHERE Appointment.AptType = 'DN'
    AND Appointment.`Status` = 'A'
    AND Appointment.PatientID = patientTable.PatientID
    AND PTCase.CaseID = cases.CaseID
) as was_discharged
FROM PTCase as cases
INNER JOIN Contact ON cases.MarketingReferral_ContactID = Contact.ContactID
INNER JOIN Patient as patientTable ON patientTable.PatientID = cases.PatientID
INNER JOIN Appointment ON Appointment.CaseID = cases.CaseID
INNER JOIN FacilityHasPatient ON FacilityHasPatient.Fcltyhpnt_PatientID = patientTable.PatientID
INNER JOIN Facility ON FacilityHasPatient.Fcltyhpnt_FacilityID = Facility.Fclty_FacilityID
INNER JOIN Company ON Facility.Fclty_CompanyID = Company.Cmp_CompanyID
WHERE patientTable.created BETWEEN '{STARTDATE}' AND '{ENDDATE}'
GROUP BY referral_id LIMIT 10;
QUERY;


    protected $physicianQuery = <<<QUERY
SELECT Patient.PatientID,
    Patient.created,
    FacilityHasPatient.Fcltyhpnt_FacilityID AS facilityId,
    Physician.PhysicianID
    , Count( distinct Patient.PatientID) AS myCount
    , Physician.*
    , PhysicianType.*
    , concat( Physician.LastName , ', ' , Physician.FirstName ) SortName
FROM Patient
JOIN FacilityHasPatient ON
Patient.PatientID = FacilityHasPatient.Fcltyhpnt_PatientID
JOIN PTCase ON
Patient.PatientID = PTCase.PatientID
AND Patient.PatientStatus IN ('A','D')
JOIN Physician ON
Physician.PhysicianID IN (PTCase.PhysicianID)
LEFT JOIN PhysicianType ON
Physician.DrType=PhysicianType.PhysicianTypeID
WHERE PTCase.Status IN ('A','D')
 AND Patient.created BETWEEN '{STARTDATE}' AND '{ENDDATE}'
GROUP BY Physician.PhysicianID
ORDER BY myCount DESC , SortName asc LIMIT 10;
QUERY;


    /**
     * Ingest data into druid.
     *
     * @param string $start ISO DateTime for start of ingestion window
     * @param string $end ISO DateTime for end of ingestion window
     * @return string
     */
    public function ingest($start = '2000-01-01T00:00:01', $end = '3030-01-01T00:00:01')
    {
        $this->setTimeWindow( $start, $end );

        $dataBatch = $this->fetch();

        $exampleData = print_r( $dataBatch[ 0 ], true );


        return "Fetched " . count($dataBatch) . " referrals.\nOne referral looks like: " . $exampleData . "\n";
    }


    public function fetch()
    {

        $mysqli = new mysqli($this->host, $this->user, $this->pass, $this->db);

        // Check connection
        if ($mysqli->connect_errno) {
            throw new \Exception( sprintf("Connect failed: %s\n", $mysqli->connect_error) );
        }

        echo "Connected.\n";

        $preparedQuery = $this->prepareQuery( $this->physicianQuery, $this->timeWindowStart, $this->timeWindowEnd );

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

    /**
     * (Optionally) transform the data for ingestion.
     *
     * @param $input
     * @return mixed $output
     */
    public function transform($input)
    {
        // TODO: Implement transform() method.
        return $input;
    }
}