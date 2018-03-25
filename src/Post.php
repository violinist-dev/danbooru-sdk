<?php

namespace DesuProject\DanbooruSdk;

use DateTime;
use DesuProject\ChanbooruInterface\PostInterface;

class Post implements PostInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var DateTime
     */
    private $creationDate;

    /**
     * @var string
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
     * @var File[]
     */
    private $files;

    /**
     * @var Tag[]
     */
    private $tags;

    /**
     * Post constructor.
     *
     * @param int $id
     * @param DateTime $creationDate
     * @param string $hash
     * @param int $rating
     * @param int $score
     * @param int $status
     * @param File[] $files
     * @param Tag[] $tags
     */
    public function __construct(
        int $id,
        DateTime $creationDate,
        string $hash,
        int $rating,
        int $score,
        int $status,
        array $files,
        array $tags
    ) {
        $this->id = $id;
        $this->creationDate = $creationDate;
        $this->hash = $hash;
        $this->rating = $rating;
        $this->score = $score;
        $this->status = $status;
        $this->files = $files;
        $this->tags = $tags;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     *
     * @return File[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * {@inheritdoc}
     *
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
