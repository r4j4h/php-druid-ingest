<?php
/**
 * Referral Ingestion Command
 *
 * Utilizes ReferralBatchIngester aka PhpDruidIngest\ReferralBatchIngester giving it the passed CLI parameters.
 *
 */


namespace ReferralIngester\Command;

use DruidFamiliar\QueryExecutor\DruidNodeDruidQueryExecutor;
use PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher;
use PhpDruidIngest\Preparer\LocalFilePreparer;
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
        $fetcher->setMySqlCredentials($this->host, $this->user, $this->pass, $this->db);
        $fetcher->setTimeWindow( $formattedStartTime, $formattedEndTime );

        $preparer = new LocalFilePreparer();

        $indexTaskQueryGenerator = new SimpleIndexQueryGenerator();

        $indexTaskQueryParameters = new IndexTaskQueryParameters();

        $basicDruidJobWatcher = new IndexingTaskDruidJobWatcher();
        $basicDruidJobWatcher->setDruidIp( $this->druidIp );
        $basicDruidJobWatcher->setDruidPort( $this->druidPort );

        $druidQueryExecutor = new DruidNodeDruidQueryExecutor($this->druidIp, $this->druidPort, '/druid/indexer/v1/task');

        try {

            $fetchedData = $fetcher->fetch();

            ////////////////////////////////
            $exampleData = print_r( $fetchedData[ 0 ], true );
            $output->writeln( "Fetched " . count($fetchedData) . " referrals.\nOne referral looks like: " . $exampleData . "\n" );
            ////////////////////////////////

            $pathOfPreparedData = $preparer->prepare($fetchedData);

            $indexBody = $indexTaskQueryGenerator->generateIndex( $pathOfPreparedData, $indexTaskQueryParameters );

            $ingestionTaskId = $druidQueryExecutor->executeQuery($indexTaskQueryGenerator, $indexTaskQueryParameters, new IndexingTaskResponseHandler());

            var_dump('IndexTaskResponseHandler returned task id:');
            var_dump( $ingestionTaskId );

            $success = $basicDruidJobWatcher->watchJob( $ingestionTaskId );

            $output->writeln( $success );

        } catch ( \Exception $e ) {
            throw $e;
        }

    }

}