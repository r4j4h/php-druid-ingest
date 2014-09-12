<?php

require_once('vendor/autoload.php');

$ingester = new PhpDruidIngest\ReferralBatchIngester();

$ingester->setMySqlCredentials("devdb101", "webpt_druid", "2x0hKHdXNBrXDMJ", "dev_app_webpt_com");

$response = $ingester->ingest('2008-01-01 00:00:01', '2009-01-01 00:00:01');

var_dump( $response );



