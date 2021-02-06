<?php

namespace App\Connectors;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use Throwable;

class HostawayConnector implements HostawayConnectorInterface
{
    /**
     * @var Client Guzzle http client.
     */
    protected Client $client;

    /**
     * Response status.
     */
    private const STATUS_SUCCESS = 'success';

    /**
     * HostawayConnector constructor.
     *
     * @param string $baseUri
     * @param int    $cacheTime
     */
    public function __construct(
        protected string $baseUri = 'https://api.hostaway.com/',
        protected int $cacheTime = 3600
    )
    {
        $this->client = new Client(['base_uri' => $this->baseUri]);
    }

    /**
     * Get country codes list from Hostaway API.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCountryCodes(): array
    {
        return $this->getListByUrlCache('countries');
    }

    /**
     * Get time zones list from Hostaway API.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getTimeZones(): array
    {
        return $this->getListByUrlCache('timezones');
    }


    /**
     * Get list of data by Url.
     *
     * @param string $url API url
     * @return array
     * @throws RuntimeException|InvalidArgumentException
     */
    protected function getListByUrlCache(string $url): array
    {
        $cacheKey = md5($url);

        try {

            return Cache::store('redis')->get($cacheKey, function () use ($url, $cacheKey) {
                $data = $this->httpGetDataByUrl($url);

                Cache::store('redis')->put($cacheKey, $data, $this->cacheTime);

                return $data;
            });

        } catch (Throwable $e) {

            throw new RuntimeException('Request error: ' . $e->getMessage());
        }
    }

    /**
     * Get response (array) by Url.
     *
     * @param string $url API url
     * @return array
     * @throws GuzzleException|RuntimeException|Exception
     */
    protected function httpGetDataByUrl(string $url): array
    {
       $response = $this->client->get($url);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Request error.');
        }

        $responseBody = $response->getBody();

        $data = json_decode((string)$responseBody, true, 512, JSON_THROW_ON_ERROR);

        if ($data === false) {
            throw new RuntimeException('Invalid json.');
        }

        if ($data['status'] !== self::STATUS_SUCCESS) {
            throw new RuntimeException('Response status is not successful.');
        }

        if (!$data['result']) {
            throw new RuntimeException('Response result is empty.');
        }

        return array_keys($data['result']);
    }
}
