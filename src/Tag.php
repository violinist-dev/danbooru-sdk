<?php

namespace DesuProject\DanbooruSdk;

use DesuProject\ChanbooruInterface\TagInterface;

class Tag implements TagInterface
{
    /**
     * @var int
     */
    private $id;

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

    public function __construct(
        int $id,
        string $title,
        int $amountOfPosts,
        int $type
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->amountOfPosts = $amountOfPosts;
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAmountOfPosts(): int
    {
        return $this->amountOfPosts;
    }

    public function getType(): int
    {
        return $this->type;
    }
}
