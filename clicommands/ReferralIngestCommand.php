<?php
/**
 * Referral Ingestion Command
 *
 * Utilizes ReferralBatchIngester aka PhpDruidIngest\ReferralBatchIngester giving it the passed CLI parameters.
 *
 */


namespace ReferralIngester\Command;
use PhpDruidIngest\BaseDruidTaskExecutor;
use PhpDruidIngest\BasePreparer;
use PhpDruidIngest\ReferralBatchIngester;
use PhpDruidIngest\SimpleIndexGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ReferralIngestCommand runs ReferralBatchIngester giving it the passed CLI parameters.
 *
 * @author Jasmine Hegman <jasmine@webpt.com>
 */
class ReferralIngestCommand extends IngestCommand
{

    public function __construct($dbConfig, $name = 'referral-ingest') {
        $this->commandName = $name;
        $this->host = $dbConfig['host'];
        $this->user = $dbConfig['user'];
        $this->pass = $dbConfig['pass'];
        $this->db = $dbConfig['db'];
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
        $ingester = new ReferralBatchIngester();
        $ingester->setMySqlCredentials($this->host, $this->user, $this->pass, $this->db);
        $ingester->setTimeWindow( $formattedStartTime, $formattedEndTime );

        $preparer = new BasePreparer();

        $indexGenerator = new SimpleIndexGenerator();

        $taskRunner = new BaseDruidTaskExecutor();

        $druidConnection = 1; // TODO Define -- use from php-druid-query

        try {

            $response = $ingester->ingest();

            $ingestedData = $response;

            // TODO Prepare
            $pathOfPreparedData = $preparer->prepare($ingestedData);

            // TODO Generate Index
            $indexBody = $indexGenerator->generateIndex( $pathOfPreparedData, $dimensionData );

            // TODO Task Runner
            $success = $taskRunner->index( $druidConnection, $indexBody );

            $output->writeln( $ingestedData );

        } catch ( \Exception $e ) {
            throw $e;
        }

    }

}