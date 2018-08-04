<?php

namespace DesuProject\DanbooruSdk;

use DateTime;
use DesuProject\ChanbooruInterface\Exception\PostNotFoundException;
use DesuProject\ChanbooruInterface\FileInterface;
use DesuProject\ChanbooruInterface\PostInterface;
use InvalidArgumentException;

class Post implements PostInterface
{
    const ENDPOINT = '/posts.json';

    /**
     * @var int
     */
    private $id;

    /**
     * @var DateTime
     */
    private $creationDate;

    /**
     * @var null|string
     */
    private $hash;

    /**
     * @var int
     */
    private $rating;

    /**
     * @var int
     */
    private $score;

    /**
     * @var int
     */
    private $status;

    /**
     * @var null|string
     */
    private $previewImageUrl;

    /**
     * @var null|File
     */
    private $sourceFile;

    /**
     * @var null|string
     */
    private $source;

    /**
     * @var Tag[]
     */
    private $tags;

    /**
     * Post constructor.
     *
     * @param int         $id
     * @param DateTime    $creationDate
     * @param null|string $hash
     * @param int         $rating
     * @param int         $score
     * @param int         $status
     * @param null|string $previewImageUrl
     * @param null|File   $sourceFile
     * @param string|null $source
     * @param Tag[]       $tags
     *
     * @throws InvalidArgumentException if invalid argument passed
     */
    public function __construct(
        int $id,
        DateTime $creationDate,
        ?string $hash,
        int $rating,
        int $score,
        int $status,
        ?string $previewImageUrl,
        ?File $sourceFile,
        ?string $source,
        array $tags
    ) {
        $this->id = $id;
        $this->creationDate = $creationDate;
        $this->hash = $hash;
        $this->rating = $rating;
        $this->score = $score;
        $this->status = $status;
        $this->previewImageUrl = $previewImageUrl;
        $this->sourceFile = $sourceFile;
        $this->source = $source;
        $this->tags = $tags;

        if (!self::isValidId($id)) {
            throw new InvalidArgumentException('ID is invalid');
        }

        if (!self::isValidHash($hash)) {
            throw new InvalidArgumentException('Hash is invalid');
        }

        if (!self::isValidRating($rating)) {
            throw new InvalidArgumentException('Rating is invalid');
        }

        if (!self::isValidStatus($status)) {
            throw new InvalidArgumentException('Status is invalid');
        }

        if (!self::isValidPreviewImageUrl($previewImageUrl)) {
            throw new InvalidArgumentException('Preview image URL is invalid');
        }

        if (!self::isValidSource($source)) {
            throw new InvalidArgumentException('Source is invalid');
        }

        foreach ($tags as $tag) {
            if (!$tag instanceof Tag) {
                throw new InvalidArgumentException('Not all elements of array are Tag objects');
            }
        }
    }

    /**
     * Search posts.
     *
     * @param Client $client
     * @param array  $tags
     * @param int    $page
     * @param int    $limit
     *
     * @return array
     */
    public static function search(
        Client $client,
        array $tags,
        int $page,
        int $limit = 20
    ): array {
        if (!self::isValidPageNumber($page)) {
            throw new InvalidArgumentException('Page number should be greater than zero');
        }

        if (!self::isValidLimitation($page)) {
            throw new InvalidArgumentException('Limit should be greater than zero');
        }

        $query = [
            'page' => $page,
            'limit' => $limit,
            'tags' => implode(',', $tags),
        ];

        $postsAsArrays = $client->sendRequest(
            self::ENDPOINT,
            $query
        );

        return array_map(function (array $post) use ($client): Post {
            return self::fromArray($client, $post);
        }, $postsAsArrays);
    }

    /**
     * Get certain post by its ID or throw exception if nothing found.
     *
     * @param Client $client
     * @param int    $id
     *
     * @return Post
     *
     * @throws PostNotFoundException if post not found
     */
    public static function byId(
        Client $client,
        int $id
    ): Post {
        $posts = self::search($client, ['id:' . $id], 1, 1);

        if (count($posts) === 0) {
            throw new PostNotFoundException(sprintf(
                'Post with ID %d not found',
                $id
            ));
        }

        return $posts[0];
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return DateTime
     */
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|File
     */
    public function getSourceFile(): ?FileInterface
    {
        return $this->sourceFile;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|string
     */
    public function getPreviewImageUrl(): ?string
    {
        return $this->previewImageUrl;
    }

    /**
     * {@inheritdoc}
     *
     * @return Tag[]
     */
    public function getTags(int $type = null): array
    {
        if ($type === null) {
            return $this->tags;
        } else {
            return array_filter($this->tags, function (Tag $tag) use ($type): bool {
                return $tag->getType() === $type;
            });
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return null|string
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isPostCensored(): bool
    {
        return isset($post['file_ext']);
    }

    /**
     * Creates Post instance from API response.
     *
     * @param Client $client
     * @param array  $post
     *
     * @return Post
     */
    protected static function fromArray(
        Client $client,
        array $post
    ): Post {
        $originalFile = isset($post['file_ext'])
            ? File::fromArray($post)
            : null;

        $tags = Tag::byNames($client, explode(' ', $post['tag_string']));

        return new Post(
            $post['id'],
            DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $post['created_at']),
            $post['md5'] ?? null,
            self::getRatingByStringIdentifier($post['rating']),
            $post['score'],
            self::getStatusByBooleanFlags($post['is_pending'], $post['is_deleted'], $post['is_banned']),
            $post['preview_file_url'] ?? null,
            $originalFile,
            $post['source'],
            $tags
        );
    }

    /**
     * Converts API rating identifier to constant value.
     *
     * @param string $identifier
     *
     * @return int
     */
    protected static function getRatingByStringIdentifier(string $identifier): int
    {
        switch ($identifier) {
            case 's':
                return PostInterface::RATING_SAFE;

            case 'q':
                return PostInterface::RATING_QUESTIONABLE;

            case 'e':
                return PostInterface::RATING_EXPLICIT;

            default:
                throw new InvalidArgumentException('Unknown rating identifier');
        }
    }

    /**
     * Returns constant value by post status flags.
     *
     * @param bool $isPending
     * @param bool $isDeleted
     * @param bool $isBanned
     *
     * @return int
     */
    protected static function getStatusByBooleanFlags(
        bool $isPending,
        bool $isDeleted,
        bool $isBanned
    ): int {
        if ($isBanned === true) {
            return PostInterface::STATUS_DELETED;
        }

        if ($isDeleted === true) {
            return PostInterface::STATUS_DELETED;
        }

        if ($isPending === true) {
            return PostInterface::STATUS_PENDING_MODERATION;
        }

        return PostInterface::STATUS_PUBLISHED;
    }

    protected static function isValidPageNumber(int $page): bool
    {
        return $page > 0;
    }

    protected static function isValidLimitation(int $limit): bool
    {
        return $limit > 0;
    }

    protected static function isValidId(int $id): bool
    {
        return $id > 0;
    }

    protected static function isValidHash(?string $hash): bool
    {
        if (is_null($hash)) {
            return true;
        }

        return preg_match('/^[a-f0-9]{32}$/', $hash) === 1;
    }

    protected static function isValidRating(int $rating): bool
    {
        return in_array($rating, [
            self::RATING_SAFE,
            self::RATING_QUESTIONABLE,
            self::RATING_EXPLICIT,
        ]);
    }

    protected static function isValidStatus(int $status): bool
    {
        return in_array($status, [
            self::STATUS_PUBLISHED,
            self::STATUS_PENDING_MODERATION,
            self::STATUS_DELETED,
        ]);
    }

    protected static function isValidPreviewImageUrl(?string $previewFileUrl): bool
    {
        if (is_null($previewFileUrl)) {
            return true;
        }

        return filter_var(
            $previewFileUrl,
            FILTER_VALIDATE_URL,
            FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED
        );
    }

    protected static function isValidSource(?string $source): bool
    {
        if (is_null($source)) {
            return true;
        }

        return filter_var(
            $source,
            FILTER_VALIDATE_URL,
            FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED
        );
    }
}
