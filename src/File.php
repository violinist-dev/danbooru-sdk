<?php

namespace DesuProject\DanbooruSdk;

use DesuProject\ChanbooruInterface\FileInterface;
use InvalidArgumentException;

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
     * File constructor.
     *
     * @param int    $size
     * @param string $url
     * @param int    $type
     * @param int    $width
     * @param int    $height
     *
     * @throws InvalidArgumentException if invalid argument passed
     */
    public function __construct(
        int $size,
        string $url,
        int $type,
        int $width,
        int $height
    ) {
        $this->size = $size;
        $this->url = $url;
        $this->type = $type;
        $this->width = $width;
        $this->height = $height;

        if (!self::isSizeValid($size)) {
            throw new InvalidArgumentException('Size is invalid');
        }

        if (!self::isUrlValid($url)) {
            throw new InvalidArgumentException('URL is invalid');
        }

        if (!self::isTypeValid($type)) {
            throw new InvalidArgumentException('Type is invalid');
        }

        if (!self::isWidthValid($width)) {
            throw new InvalidArgumentException('Width is invalid');
        }

        if (!self::isHeightValid($height)) {
            throw new InvalidArgumentException('Height is invalid');
        }
    }

    /**
     * Creates File instance from API response.
     *
     * @param array $file
     *
     * @return File
     */
    public static function fromArray(array $file): File
    {
        $url = $file['file_ext'] === 'zip'
            ? $file['large_file_url']
            : $file['file_url'];

        return new File(
            $file['file_size'],
            $url,
            self::getFileTypeByExtension($file['file_ext']),
            $file['image_width'],
            $file['image_height']
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
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
     * {@inheritdoc}
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Returns file type constant value by file extension.
     *
     * @param string $extension
     *
     * @return int
     */
    protected static function getFileTypeByExtension(string $extension): int
    {
        switch ($extension) {
            case 'png':
            case 'jpg':
            case 'gif':
                return FileInterface::TYPE_IMAGE;

            case 'webm':
            case 'mp4':
            case 'zip':
                return FileInterface::TYPE_VIDEO;

            default:
                throw new InvalidArgumentException(sprintf(
                    'Unknown file type: %s',
                    $extension
                ));
        }
    }

    protected static function isSizeValid(int $size): bool
    {
        return $size > 0;
    }

    protected static function isUrlValid(string $url): bool
    {
        return filter_var(
            $url,
            FILTER_VALIDATE_URL,
            FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED
        );
    }

    protected static function isTypeValid(int $type): bool
    {
        return in_array($type, [
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
        ]);
    }

    protected static function isWidthValid(int $width): bool
    {
        return $width > 0;
    }

    protected static function isHeightValid(int $height): bool
    {
        return $height > 0;
    }
}
