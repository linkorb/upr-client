<?php

namespace Upr\Client;

use GuzzleHttp\Client  as GuzzleClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Client
{
    private $url;
    private $username;
    private $password;
    private $GuzzleClient;

    public function __construct(string $url, string $username, string $password, FilesystemAdapter $cache)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        $this->guzzleClient = new GuzzleClient();
    }

    public static function createFromEnv(): self
    {
        $cache = new FilesystemAdapter(getenv('UPR_CACHE_PATH'));
        $uprUrlArray = parse_url(getenv('UPR_URL'));

        $url = $uprUrlArray['scheme'].'://'.$uprUrlArray['host'];
        $url .= (!empty($uprUrlArray['port'])) ? ':'.$uprUrlArray['port'] : '';
        $url .= $uprUrlArray['path'] ?? '';
        $url .= (!empty($uprUrlArray['query'])) ? '&'.$uprUrlArray['query'] : '';

        return new self($url, $uprUrlArray['user'], $uprUrlArray['pass'], $cache);
    }

    public function getFileMetadata(string $hashCode): array
    {
        try {
            $res = $this->guzzleClient->request('GET', $this->url.'/api/v1/files/'.$hashCode.'/metadata', [
                'auth' => [$this->username, $this->password],
                'headers' => [
                    ['Accept' => 'application/json'],
                ],
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return json_decode($res->getBody(), true);
    }
}
