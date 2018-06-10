<?php

namespace DesuProject\DanbooruSdk;

use DesuProject\ChanbooruInterface\Exception\TagNotFoundException;
use DesuProject\ChanbooruInterface\TagInterface;
use InvalidArgumentException;

class Tag implements TagInterface
{
    const ENDPOINT = '/tags.json';

    const ORDER_NAME = 'name';
    const ORDER_DATE = 'date';
    const ORDER_COUNT = 'count';

    const DANBOORU_TYPE_GENERAL = 0;
    const DANBOORU_TYPE_ARTIST = 1;
    const DANBOORU_TYPE_CHARACTER = 4;
    const DANBOORU_TYPE_TITLE = 3;
    const DANBOORU_TYPE_META = 5;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $amountOfPosts;

    /**
     * @var int
     */
    private $type;

    /**
     * Tag constructor.
     *
     * @param string $title
     * @param int    $amountOfPosts
     * @param int    $danbooruType
     *
     * @throws InvalidArgumentException if invalid argument passed
     */
    public function __construct(
        string $title,
        int $amountOfPosts,
        int $danbooruType
    ) {
        $this->title = $title;
        $this->amountOfPosts = $amountOfPosts;
        $this->type = self::convertDanbooruTypeToChanbooruType($danbooruType);

        if (!self::isValidAmountOfPosts($amountOfPosts)) {
            throw new InvalidArgumentException('Amount of posts is invalid');
        }
    }

    /**
     * @param Client            $client
     * @param array|null|string $names     If it's string, it will search by pattern.
     *                                     If it's array, it will search strictly by tag names.
     * @param string            $orderBy
     * @param int|null          $type
     * @param bool              $hideEmpty
     *
     * @return array
     *
     * @throws InvalidArgumentException if invalid $names argument passed
     */
    public static function search(
        Client $client,
        $names,
        string $orderBy = self::ORDER_NAME,
//        ?int $type = null,
        bool $hideEmpty = true
    ): array {
        if (!is_null($names) && !is_string($names) && !is_array($names)) {
            throw new InvalidArgumentException('$names may be only null, string or array');
        }

        if (!self::isValidOrderingMethod($orderBy)) {
            throw new InvalidArgumentException('Invalid order method');
        }

//        if (!is_null($type) && !self::isValidType($type)) {
//            throw new InvalidArgumentException('Invalid tag type');
//        }

        $query = [
            'search' => [
                'order' => $orderBy,
                'hide_empty' => $hideEmpty === true ? 'yes' : 'no',
            ],
        ];

        if (!is_null($names)) {
            if (is_string($names)) {
                $query['search']['name_matches'] = $names;
            }

            if (is_array($names)) {
                $query['search']['name'] = implode(',', $names);
            }
        }

//        if (!is_null($type)) {
//            $query['search']['category'] = $type;
//        }

        $tagsAsArrays = $client->sendRequest(
            self::ENDPOINT,
            $query
        );

        return array_map(function (array $tag): Tag {
            return self::fromArray($tag);
        }, $tagsAsArrays);
    }

    /**
     * Search tags by names.
     *
     * @param Client $client
     * @param array  $names
     * @param string $orderBy
     *
     * @return array
     */
    public static function byNames(
        Client $client,
        array $names,
        string $orderBy = self::ORDER_NAME
    ): array {
        return self::search($client, $names, $orderBy);
    }

    /**
     * Search tags by pattern.
     *
     * @param Client $client
     * @param string $namePattern @example: girl*
     * @param string $orderBy
     *
     * @return array
     */
    public static function byNamePattern(
        Client $client,
        string $namePattern,
        string $orderBy = self::ORDER_NAME
    ): array {
        return self::search($client, $namePattern, $orderBy);
    }

    /**
     * Search tag by name or throw exception if nothing found.
     *
     * @param Client $client
     * @param string $name
     *
     * @return Tag
     *
     * @throws TagNotFoundException if tag not found
     */
    public static function byName(
        Client $client,
        string $name
    ): Tag {
        $tags = self::search(
            $client,
            [$name],
            self::ORDER_NAME,
//            null,
            false
        );

        if (count($tags) === 0) {
            throw new TagNotFoundException(sprintf(
                'Tag with name %s not found',
                $name
            ));
        }

        return $tags[0];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getAmountOfPosts(): int
    {
        return $this->amountOfPosts;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Creates Tag instance from API response.
     *
     * @param array $tag
     *
     * @return Tag
     */
    protected static function fromArray(array $tag): Tag
    {
        return new Tag($tag['name'], $tag['post_count'], $tag['category']);
    }

    protected static function isValidAmountOfPosts(int $amountOfPosts): bool
    {
        return $amountOfPosts >= 0;
    }

    protected static function isValidOrderingMethod(string $orderBy): bool
    {
        return in_array($orderBy, [
            self::ORDER_NAME,
            self::ORDER_DATE,
            self::ORDER_COUNT,
        ]);
    }

    protected static function convertDanbooruTypeToChanbooruType(int $type): ?int
    {
        $types = [
            self::DANBOORU_TYPE_TITLE => self::TYPE_TITLE,
            self::DANBOORU_TYPE_CHARACTER => self::TYPE_CHARACTER,
            self::DANBOORU_TYPE_ARTIST => self::TYPE_ARTIST,
            self::DANBOORU_TYPE_META => self::TYPE_META,
        ];

        if (!isset($types[$type])) {
            return null;
        }

        return $types[$type];
    }
}
