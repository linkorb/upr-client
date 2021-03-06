<?php

use Envoi\Envoi;

include_once __DIR__.'/../vendor/autoload.php';

// .env validate it.
$envFilename = __DIR__.'/../.env';
$envMetaFile = __DIR__.'/../.env.yaml';
Envoi::init($envFilename, $envMetaFile);


if (count($argv)!=2) {
    echo "Please pass 1 parameter: hashCode\n";
    exit(-1);
}

$hashCode = $argv[1];

$upr = Upr\Client\Client::createFromEnv();
$data = $upr->getFileMetadata($hashCode);

print_r($data);
exit;
