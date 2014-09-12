<?php

require_once('vendor/autoload.php');

date_default_timezone_set('America/Denver');

use Symfony\Component\Console\Application;

use ReferralIngester\Command\IngestCommand;


$console = new Application();


$console->add( new IngestCommand() );


$console->run();


