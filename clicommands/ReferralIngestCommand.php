<?php
/**
 * Referral Ingestion Command
 *
 * Utilizes ReferralBatchIngester aka PhpDruidIngest\ReferralBatchIngester giving it the passed CLI parameters.
 *
 */


namespace ReferralIngester\Command;

use DruidFamiliar\QueryExecutor\DruidNodeDruidQueryExecutor;
use PhpDruidIngest\DruidJobWatcher\CallbackBasedIndexingTaskDruidJobWatcher;
use PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher;
use PhpDruidIngest\Preparer\LocalFilePreparer;
use PhpDruidIngest\Preparer\LocalPhpArrayToJsonFilePreparer;
use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;
use PhpDruidIngest\DruidJobWatcher\BasicDruidJobWatcher;
use PhpDruidIngest\Fetcher\ReferralBatchFetcher;
use PhpDruidIngest\QueryGenerator\SimpleIndexQueryGenerator;
use PhpDruidIngest\ResponseHandler\IndexingTaskResponseHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ReferralIngestCommand runs ReferralBatchIngester giving it the passed CLI parameters.
 *
 * @author Jasmine Hegman <jasmine@webpt.com>
 */
class ReferralIngestCommand extends IngestCommand
{

    protected $host;
    protected $user;
    protected $pass;
    protected $db;

    protected $druidIp;
    protected $druidPort;

    public function __construct($dbConfig, $druidNodeConfig, $name = 'referral-ingest' )
    {
        $this->commandName = $name;
        $this->host = $dbConfig['host'];
        $this->user = $dbConfig['user'];
        $this->pass = $dbConfig['pass'];
        $this->db = $dbConfig['db'];

        $this->druidIp = $druidNodeConfig['ip'];
        $this->druidPort = $druidNodeConfig['port'];

        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName( $this->commandName )
            ->setDescription('Run referral ingestion task for the given time window')
            ->setDefinition($this->createDefinition())
            ->setHelp(<<<HELPBLURB
Examples:
Dates:
\t<info>php ingest.php $this->commandName 2008-01-01 2009-01-01</info>
Dates with Time:
\t<info>php ingest.php $this->commandName 2008-01-01T01:30:00 2009-01-01T04:20:00 -v</info>

HELPBLURB
            );
        ;
    }


    protected function ingest($formattedStartTime, $formattedEndTime, InputInterface $input, OutputInterface $output)
    {
        $fetcher = new ReferralBatchFetcher();
        $fetcher->setOutput($output);
        $fetcher->setMySqlCredentials($this->host, $this->user, $this->pass, $this->db);
        $fetcher->setTimeWindow( $formattedStartTime, $formattedEndTime );

        $filePathToUse = '/tmp/referral_temp_' . time() . '.json';

        $preparer = new LocalPhpArrayToJsonFilePreparer();
        $preparer->setFilePath( $filePathToUse );

        $indexTaskQueryGenerator = new SimpleIndexQueryGenerator();

        $indexTaskQueryParameters = new IndexTaskQueryParameters();
        $indexTaskQueryParameters->dataSource = 'referral-report-referrals-cli-test';
        $indexTaskQueryParameters->setIntervals( $formattedStartTime, $formattedEndTime );
        $indexTaskQueryParameters->dimensions = array('referral_id', 'facility_id', 'patient_id');
        $indexTaskQueryParameters->timeDimension = 'date';
        // TODO Fill in with dimensions, etc for referrals.
        $indexTaskQueryParameters->validate();

        $basicDruidJobWatcher = new CallbackBasedIndexingTaskDruidJobWatcher();
        /**
         * @param CallbackBasedIndexingTaskDruidJobWatcher $jobWatcher
         */
        $myOnPendingCallback = function($jobWatcher) use ($output) {
            $output->writeln('Druid says the job is still running. Trying again in ' . $jobWatcher->watchAttemptDelay . ' seconds...');
        };

        $basicDruidJobWatcher->setOnJobPending($myOnPendingCallback);
        $basicDruidJobWatcher->setOutput($output);
        $basicDruidJobWatcher->setDruidIp( $this->druidIp );
        $basicDruidJobWatcher->setDruidPort( $this->druidPort );

        $druidQueryExecutor = new DruidNodeDruidQueryExecutor($this->druidIp, $this->druidPort, '/druid/indexer/v1/task');

        try {

            $output->writeln("Fetching referral data from source...");

            $fetchedData = $fetcher->fetch();

            ////////////////////////////////
            $output->writeln( "Fetched " . count($fetchedData) . " referrals." );
            if ( count( $fetchedData ) === 0 ) {
                $output->writeln( "Fetched no records, so stopping here." );
                return;
            }

            if ( $output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE ) {
                $exampleData = print_r( $fetchedData[0], true );
                $output->writeln("The first record looks like:\n\n" . $exampleData . "\n\n");
            }


            $pathOfPreparedData = $preparer->prepare($fetchedData);

            $indexTaskQueryParameters->setFilePath($pathOfPreparedData);
            $output->writeln('File is prepared for druid at "' . $pathOfPreparedData . '"');

            $output->writeln('Requesting Druid index the source data into dataSource "' . $indexTaskQueryParameters->dataSource . '"');

            $ingestionTaskId = $druidQueryExecutor->executeQuery($indexTaskQueryGenerator, $indexTaskQueryParameters, new IndexingTaskResponseHandler());
            $output->writeln('Druid has received the job. Job id is "' . $ingestionTaskId . '"');

            $output->writeln('Checking Druid for the job status:');
            $success = $basicDruidJobWatcher->watchJob( $ingestionTaskId );



            if ( $success )
            {
                $output->writeln('Done checking druid job status.');

                $output->writeln('Cleaning up prepared file "' . $pathOfPreparedData . '"...');

                $cleanedUpSuccess = $preparer->cleanup($pathOfPreparedData);

                if ( $cleanedUpSuccess ) {
                    $output->writeln('Succesfully cleaned up file.');
                }
            }
            else
            {
                $output->writeln('Job failed or did not finish in time.');
                $output->writeln('In the future I will ask you if you want to delete the temporary file but for now I will leave it in place so that ingestion is not interrupted.');
            }

            $output->writeln('Done.');

            ////////////////////////////////


        } catch ( \Exception $e ) {
            throw $e;
        }

    }

}