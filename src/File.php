<?php

namespace DesuProject\DanbooruSdk;

use DesuProject\ChanbooruInterface\FileInterface;

class File implements FileInterface
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int|null
     */
    private $duration;

    public function __construct(
        int $size,
        string $url,
        int $type,
        int $width,
        int $height,
        ?int $duration
    ) {
        $this->size = $size;
        $this->url = $url;
        $this->type = $type;
        $this->width = $width;
        $this->height = $height;
        $this->duration = $duration;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }
}
