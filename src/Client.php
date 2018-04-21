<?php

namespace DesuProject\DanbooruSdk;

use DesuProject\ChanbooruInterface\ClientInterface;
use RuntimeException;

class Client implements ClientInterface
{
    const BASE_HOST = 'https://danbooru.donmai.us';
    const BASE_SAFE_HOST = 'https://safebooru.donmai.us';

    /**
     * @var null|string
     */
    private $apiKey;

    /**
     * Send requests to safebooru.donmai.us instead of danbooru.donmai.us.
     *
     * Safebooru is a mirror of Danbooru, but it returns only safe images.
     * Danbooru may be blocked in in some countries.
     *
     * @var bool
     */
    private $isSafe;

    /**
     * Client constructor.
     *
     * @param null|string $apiKey
     * @param bool        $isSafe @see self::$isSafe
     */
    public function __construct(
        ?string $apiKey = null,
        bool $isSafe = false
    ) {
        $this->apiKey = $apiKey;
        $this->isSafe = $isSafe;
    }

    /**
     * Returns API key used for all requests.
     *
     * @return null|string
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Sends request to endpoint and returns decoded response.
     *
     * @param string $endpoint
     * @param array  $queryString
     *
     * @return mixed
     *
     * @throws RuntimeException if cURL error occurred
     */
    public function sendRequest(
        string $endpoint,
        array $queryString = []
    ) {
        $headers = [
            'Accept' => 'application/json',
        ];

        if (!is_null($this->getApiKey())) {
            $headers['Authorization'] = $this->getApiKey();
        }

        $curl = curl_init();

        $host = $this->isSafe === true
            ? self::BASE_SAFE_HOST
            : self::BASE_HOST;

        $requestPath = $host . $endpoint . '?' . http_build_query($queryString);

        curl_setopt($curl, CURLOPT_URL, $requestPath);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->convertArrayToCurlHeaders($headers));

        if (mb_strlen($curlError = curl_error($curl)) > 0) {
            throw new RuntimeException(sprintf(
                'cURL request error: %s',
                $curlError
            ));
        }

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }

    private function convertArrayToCurlHeaders(array $headers): array
    {
        $curlHeaders = [];

        foreach ($headers as $headerName => $headerValue) {
            $curlHeaders[] = sprintf('%s: %s', $headerName, $headerValue);
        }

        return $curlHeaders;
    }
}
