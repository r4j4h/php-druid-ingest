<?php
/**
 * A base command to wrap common ingestion tasks. Handles getting start/end time window arguments.
 */


namespace ReferralIngester\Command;

use DruidFamiliar\DruidTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * IngestCommand is a base command to wrap common ingestion tasks. Handles getting start/end time window arguments.
 *
 * @author Jasmine Hegman <jasmine@webpt.com>
 */
abstract class IngestCommand extends Command
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
            ->setHelp(<<<HELPBLURB
Examples:
Dates:
\t<info>php ingest.php ingest 2008-01-01 2009-01-01</info>
Dates with Time:
\t<info>php ingest.php ingest 2008-01-01T01:30:00 2009-01-01T04:20:00 -v</info>

HELPBLURB
            );
        ;
    }

    abstract protected function ingest($formattedStartTime, $formattedEndTime, InputInterface $input, OutputInterface $output);

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputStart = ( $input->getArgument('start') );
        $inputEnd = ( $input->getArgument('end') );

        $startTime = new DruidTime( $inputStart );
        $endTime = new DruidTime( $inputEnd );

        $formattedStartTime = $startTime->formatTimeForDruid();
        $formattedEndTime = $endTime->formatTimeForDruid();

        $output->write("<info>Ingesting data</info>");
        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->write( " for the period <info>$formattedStartTime</info> to <info>$formattedEndTime</info>" );
        }
        $output->write( "\n" );

        $this->ingest($formattedStartTime, $formattedEndTime, $input, $output);

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
    protected function createDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('start',  InputArgument::REQUIRED,    'Start Time of Ingestion Window as ISO String'),
            new InputArgument('end',    InputArgument::REQUIRED,    'End Time of Ingestion Window as ISO String'),
        ));
    }
}