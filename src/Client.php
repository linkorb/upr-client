<?php

namespace Upr\Client;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class Client
{
    private $url;
    private $username;
    private $password;
    private $GuzzleClient;

    private function __construct(string $url, string $username, string $password, ?CacheInterface $cache = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        $this->guzzleClient = new GuzzleClient();
    }

    public static function createFromEnv(): self
    {
        $uprCache = getenv('UPR_CACHE');
        if (!empty($uprCache) && file_exists($uprCache)) {
            $cache = new FilesystemAdapter('', 0, $uprCache);
        } else {
            $cache = new PhpArrayAdapter('upr_cache.php',new FilesystemAdapter());
        }

        $uprUrlArray = parse_url(getenv('UPR_URL'));

        $url = $uprUrlArray['scheme'].'://'.$uprUrlArray['host'];
        $url .= (!empty($uprUrlArray['port'])) ? ':'.$uprUrlArray['port'] : '';
        $url .= $uprUrlArray['path'] ?? '';
        $url .= (!empty($uprUrlArray['query'])) ? '&'.$uprUrlArray['query'] : '';

        return new self($url, $uprUrlArray['user'], $uprUrlArray['pass'], $cache);
    }

    public function getFileMetadata(string $hashCode): array
    {
        $res = $this->guzzleClient->request('GET', $this->url.'/api/v1/files/'.$hashCode.'/metadata', [
                'auth' => [$this->username, $this->password],
                'headers' => [
                    ['Accept' => 'application/json'],
                ],
            ]);

        return json_decode($res->getBody(), true);
    }
}
