<?php

namespace DesuProject\DanbooruSdk;

use DesuProject\ChanbooruInterface\ClientInterface;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    private $apiKey;

    public function __construct(
        ?string $apiKey = null
    ) {
        $this->setApiKey($apiKey);
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    public function search(
        array $tags = [],
        ?int $page = null,
        ?int $limit = null
    ): array {

    }

    /**
     * @param string $url
     * @param string $httpMethod
     */
    private function sendRequest(
        string $url,
        string $httpMethod,
        array $queryString = [],
        array $headers
    ) {
        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->getApiKey() !== null) {
            $headers['Authorization'] = $this->getApiKey();
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->convertArrayToCurlHeaders($headers));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    private function convertArrayToCurlHeaders(array $headers): array
    {
        return array_map(function ($header) {

        }, $headers);
    }
}
