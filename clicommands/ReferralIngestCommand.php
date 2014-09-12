<?php


namespace ReferralIngester\Command;
use PhpDruidIngest\ReferralBatchIngester;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * IngestCommand runs ReferralBatchIngester
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ReferralIngestCommand extends IngestCommand
{

    public function __construct($dbConfig) {
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
            ->setName('referral-ingest')
            ->setDescription('Run referral ingestion task for the given time window')
            ->setDefinition($this->createDefinition())
            ->setHelp(<<<HELPBLURB
Examples:
Dates:
\t<info>php ingest.php referral-ingest 2008-01-01 2009-01-01</info>
Dates with Time:
\t<info>php ingest.php referral-ingest 2008-01-01T01:30:00 2009-01-01T04:20:00 -v</info>

HELPBLURB
            );
        ;
    }


    protected function ingest($formattedStartTime, $formattedEndTime, InputInterface $input, OutputInterface $output)
    {
        $ingester = new ReferralBatchIngester();
        $ingester->setMySqlCredentials($this->host, $this->user, $this->pass, $this->db);

        try {

            $response = $ingester->ingest( $formattedStartTime, $formattedEndTime );

            $output->writeln( $response );

        } catch ( \Exception $e ) {
            throw $e;
        }

    }

}