<?php

require_once('vendor/autoload.php');

$ingester = new PhpDruidIngest\ReferralBatchIngester();

$ingester->setMySqlCredentials("devdb101", "webpt_druid", "2x0hKHdXNBrXDMJ", "dev_app_webpt_com");

$response = $ingester->ingest();

var_dump( $response );



