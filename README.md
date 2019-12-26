# upr-client
Upr Client Library

## Config
 ```
 # run comand to create file
 cp .env.dist .env # Create config file from template/.dist file
edit .env # Edit configuration settings etc.
```

## Use

```php
$upr = Upr\Client\Client::createFromEnv();
$data = $upr->getFileMetadata('Dhk3ckjh2X'); //pass hash key and get metadata.
```