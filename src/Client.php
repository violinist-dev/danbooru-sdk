<?php

declare(strict_types=1);

namespace DesuProject\DanbooruSdk;

use DesuProject\ChanbooruInterface\ClientInterface;
use DesuProject\ChanbooruInterface\Exception\PostNotFoundException;
use DesuProject\ChanbooruInterface\Exception\TagNotFoundException;
use DesuProject\ChanbooruInterface\PostInterface;
use DesuProject\ChanbooruInterface\TagInterface;
use InvalidArgumentException;
use RuntimeException;

class Client implements ClientInterface
{
    public const BASE_HOST = 'https://danbooru.donmai.us';
    public const BASE_SAFE_HOST = 'https://safebooru.donmai.us';

    /**
     * @var null|string
     */
    private $apiKey;

    /**
     * Function which accepts cURL resource as first argument.
     * May be used to globally configure cURL requests (e.g. for setting proxy).
     * Some cURL parameters can not be reconfigured.
     *
     * @var callable|null
     */
    private $curlConfigurator;

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
     * @param bool        $isSafe           @see self::$isSafe
     * @param callable    $curlConfigurator @see self::$curlConfigurator
     */
    public function __construct(
        ?string $apiKey = null,
        bool $isSafe = false,
        ?callable $curlConfigurator = null
    ) {
        $this->apiKey = $apiKey;
        $this->isSafe = $isSafe;
        $this->curlConfigurator = $curlConfigurator;
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
     * Get certain post by its ID or throw exception if nothing found.
     *
     * @param int $id
     *
     * @throws PostNotFoundException if post not found
     *
     * @return Post
     */
    public function getPostById(
        int $id
    ): PostInterface {
        return Post::byId($this, $id);
    }

    /**
     * Search posts.
     *
     * @param string[] $tags
     * @param int      $page
     * @param int      $limit
     *
     * @return Post[]
     */
    public function searchPosts(
        array $tags,
        int $page,
        int $limit = 20
    ): array {
        return Post::search($this, $tags, $page, $limit);
    }

    /**
     * Search tag by name or throw exception if nothing found.
     *
     * @param string $name
     *
     * @throws TagNotFoundException if tag not found
     *
     * @return Tag
     */
    public function searchTagByName(
        string $name
    ): TagInterface {
        return Tag::byName($this, $name);
    }

    /**
     * @param array|null|string $names     if it's string, it will search by pattern.
     *                                     If it's array, it will search strictly by tag names
     * @param string            $orderBy
     * @param bool              $hideEmpty
     *
     * @throws InvalidArgumentException if invalid $names argument passed
     *
     * @return Tag[]
     */
    public function searchTags(
        $names,
        string $orderBy,
        bool $hideEmpty = true
    ): array {
        return Tag::search($this, $names, $orderBy, $hideEmpty);
    }

    /**
     * Search tags by pattern.
     *
     * @param string $namePattern @example: girl*
     * @param string $orderBy
     *
     * @return Tag[]
     */
    public function searchTagsByNamePattern(
        string $namePattern,
        string $orderBy
    ): array {
        return Tag::byNamePattern($this, $namePattern, $orderBy);
    }

    /**
     * Search tags by names.
     *
     * @param string[] $names
     * @param string   $orderBy
     *
     * @return Tag[]
     */
    public function searchTagsByNames(
        array $names,
        string $orderBy
    ): array {
        return Tag::byNames($this, $names, $orderBy);
    }

    /**
     * Sends request to endpoint and returns decoded response.
     *
     * @param string $endpoint
     * @param array  $queryString
     *
     * @throws RuntimeException if cURL error occurred
     *
     * @return mixed
     */
    public function sendRequest(
        string $endpoint,
        array $queryString = []
    ) {
        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->getApiKey() !== null) {
            $headers['Authorization'] = base64_encode($this->getApiKey());
        }

        $curl = curl_init();

        $host = $this->isSafe === true
            ? self::BASE_SAFE_HOST
            : self::BASE_HOST;

        $requestPath = $host . $endpoint . '?' . http_build_query($queryString);

        if ($this->curlConfigurator !== null) {
            ($this->curlConfigurator)($curl);
        }

        curl_setopt($curl, CURLOPT_URL, $requestPath);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->convertArrayToCurlHeaders($headers));

        $response = curl_exec($curl);

        if (mb_strlen($curlError = curl_error($curl)) > 0) {
            throw new RuntimeException(sprintf(
                'cURL request error: %s',
                $curlError
            ));
        }

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
