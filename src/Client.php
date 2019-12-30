<?php

namespace Upr\Client;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;
use RuntimeException;

class Client
{
    private $url;
    private $username;
    private $password;
    private $guzzleClient;
    private $cache;
    const TTL = 0;

    private function __construct(string $url, string $username, string $password, CacheInterface $cache)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        $this->guzzleClient = new GuzzleClient();
        $this->cache = $cache;
    }

    public static function createCache(string $dsn): CacheInterface
    {
        // Sanity check if $dsn is a valid URL
        if (filter_var($dsn, FILTER_VALIDATE_URL) === FALSE) {
            throw new RuntimeException("Cache DSN is not a valid URL: " . $dsn);
        }
        $part = parse_url($dsn);
        switch ($part['scheme']) {
            case 'file':
                $path = $dsn;
                if (!file_exists($path)) {
                    throw new RuntimeException("Cache path does not exist: " . $path);
                }
                return new Psr16Cache(new FilesystemAdapter('', self::TTL, $path));
            case 'array':
                return new Psr16Cache(new ArrayAdapter(self::TTL, true));
        }
        throw new RuntimeException("Cache DSN specifies unsupported scheme: " . $part['scheme']);
    }

    public static function createFromEnv(): self
    {
        $uprCacheDsn = getenv('UPR_CACHE');
        if (!$uprCacheDsn) {
            $uprCacheDsn = 'array://null'; // default fallback
        }
        $cache = self::createCache($uprCacheDsn);
        
        $uprUrlArray = parse_url(getenv('UPR_URL'));

        $url = $uprUrlArray['scheme'].'://'.$uprUrlArray['host'];
        $url .= (!empty($uprUrlArray['port'])) ? ':'.$uprUrlArray['port'] : '';
        $url .= $uprUrlArray['path'] ?? '';
        $url .= (!empty($uprUrlArray['query'])) ? '&'.$uprUrlArray['query'] : '';

        return new self($url, $uprUrlArray['user'], $uprUrlArray['pass'], $cache);
    }

    public function getFileMetadata(string $hashCode): array
    {
        if ($this->cache->has($hashCode)) {
            // cache hit
            return $this->cache->get($hashCode);
        }
        // cache miss
        $res = $this->guzzleClient->request('GET', $this->url.'/api/v1/files/'.$hashCode.'/metadata', [
                'auth' => [$this->username, $this->password],
                'headers' => [
                    ['Accept' => 'application/json'],
                ],
            ]);

        $data = json_decode($res->getBody(), true);
        $this->cache->set($hashCode, $data);
        return $data;
    }
}
