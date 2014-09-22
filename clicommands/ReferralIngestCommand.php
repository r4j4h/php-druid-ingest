<?php
/**
 * Referral Ingestion Command
 *
 * Utilizes ReferralBatchIngester aka PhpDruidIngest\ReferralBatchIngester giving it the passed CLI parameters.
 *
 */


namespace ReferralIngester\Command;
use DruidFamiliar\QueryExecutor\DruidNodeDruidQueryExecutor;
use DruidFamiliar\ResponseHandler\DoNothingResponseHandler;
use DruidFamiliar\Test\ResponseHandler\DoNothingResponseHandlerTest;
use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;
use PhpDruidIngest\DruidJobWatcher\BasicDruidJobWatcher;
use PhpDruidIngest\Abstracts\BasePreparer;
use PhpDruidIngest\Fetcher\ReferralBatchFetcher;
use PhpDruidIngest\QueryGenerator\SimpleIndexQueryGenerator;
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

    public function __construct($dbConfig, $druidNodeConfig, $name = 'referral-ingest' ) {
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
        $ingester = new ReferralBatchFetcher();
        $ingester->setMySqlCredentials($this->host, $this->user, $this->pass, $this->db);
        $ingester->setTimeWindow( $formattedStartTime, $formattedEndTime );

        $preparer = new BasePreparer();

        $indexGenerator = new SimpleIndexQueryGenerator();

        $dimensionData = new IndexTaskQueryParameters();

        $taskRunner = new BasicDruidJobWatcher();

        $druidConnection = new DruidNodeDruidQueryExecutor($this->druidIp, $this->druidPort, '/druid/indexer/v1/task');

        try {

            $response = $ingester->ingest();

            $ingestedData = $response;

            // TODO Prepare
            $pathOfPreparedData = $preparer->prepare($ingestedData);

            // TODO Generate Index
            $indexBody = $indexGenerator->generateIndex( $pathOfPreparedData, $dimensionData );

            $ingestionTaskId = $druidConnection->executeQuery($indexGenerator, $dimensionData, new DoNothingResponseHandler());

            // TODO Task Runner
            $success = $taskRunner->watchJob( $ingestionTaskId );

            $output->writeln( $ingestedData );

        } catch ( \Exception $e ) {
            throw $e;
        }

    }

}