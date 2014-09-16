<?php

require_once('vendor/autoload.php');

date_default_timezone_set('America/Denver');

use Symfony\Component\Console\Application;

use ReferralIngester\Command\ReferralIngestCommand;


// Read configs for Ingestion's DB access
$configuration = @include('config/config.php');
if  ( !isset( $configuration['referral-ingestion'] ) )
{
    throw new \Exception('Malformed or missing configuration. Need referral-ingestion configuration.');
}

$referralIngestCommandDbConfig = $configuration['referral-ingestion']['app-database'];

$configuredIngestionCommand = new ReferralIngestCommand( $referralIngestCommandDbConfig, 'referral-ingest' );


$console = new Application();

$console->add( $configuredIngestionCommand );

$console->run();


