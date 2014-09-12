<?php


namespace ReferralIngester\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * IngestCommand runs ReferralBatchIngester
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IngestCommand extends Command
{


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ingest')
            ->setDescription('Run hardcoded ingestion task for the given time window')
            ->setDefinition($this->createDefinition())
            ->setHelp("help infoo yo")
        ;
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputStart = ( $input->getArgument('start') );
        $inputEnd = ( $input->getArgument('end') );

        $startTime = new \DateTime( $inputStart );
        $endTime = new \DateTime( $inputEnd );

        $formattedStartTime = $startTime->format(DATE_ISO8601);
        $formattedEndTime = $endTime->format(DATE_ISO8601);

        $output->write("<info>Ingesting referrals</info>");
        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->write( " for the period <info>$formattedStartTime</info> to <info>$formattedEndTime</info>" );
        }
        $output->write( "\n" );

        // TODO Move credentials to config file
        $ingester = new \PhpDruidIngest\ReferralBatchIngester();

        $ingester->setMySqlCredentials("devdb101", "webpt_druid", "2x0hKHdXNBrXDMJ", "dev_app_webpt_com");

        try {
            $response = $ingester->ingest( $formattedStartTime, $formattedEndTime );
            var_dump( $response );

        } catch ( \Exception $e ) {
            //$output->writeln('<error>' . $e . '</error>');
            throw $e;
        }


//        $dir = $input->getArgument('dir');

//        $output->writeln(sprintf('Dir listing for <info>%s</info>', $dir));
    }


    /**
     * {@inheritdoc}
     */
    public function getNativeDefinition()
    {
        return $this->createDefinition();
    }


    /**
     * {@inheritdoc}
     */
    private function createDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('start',  InputArgument::REQUIRED,    'Start Time of Ingestion Window as ISO String'),
            new InputArgument('end',    InputArgument::REQUIRED,    'End Time of Ingestion Window as ISO String'),
        ));
    }
}