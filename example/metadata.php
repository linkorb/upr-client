<?php

use Symfony\Component\Dotenv\Dotenv;

include_once __DIR__.'/../vendor/autoload.php';

$dotenv = new Dotenv(true);
$dotenv->loadEnv(__DIR__.'/../.env');
$hashCode = 'xxxx';

$upr = Upr\Client\Client::createFromEnv();
$data = $upr->getFileMetadata($hashCode);

print_r($data);
exit;
