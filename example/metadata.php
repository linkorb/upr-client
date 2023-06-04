<?php

use Envoi\EnvChecker;
use Envoi\Envoi;
use Symfony\Component\Dotenv\Dotenv;

include_once __DIR__.'/../vendor/autoload.php';

// .env validate it.
/*
$envFilename = __DIR__.'/../.env';
$envMetaFile = __DIR__.'/../.env.yaml';
Envoi::init($envFilename, $envMetaFile);
*/
(new Dotenv(false))->loadEnv(__DIR__.'/../.env');
// check the env!
(new EnvChecker())->check(__DIR__.'/../.env.yaml');

if (2 != count($argv)) {
    echo "Please pass 1 parameter: hashCode\n";
    exit(-1);
}

$hashCode = $argv[1];

$upr = Upr\Client\Client::createFromEnv();
$data = $upr->getFileMetadata($hashCode);

print_r($data);
exit;
