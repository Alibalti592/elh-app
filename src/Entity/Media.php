<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 */
#[ORM\Table(name: 'media')]
#[ORM\Entity(repositoryClass: 'App\Repository\MediaRepository')]
class Media {

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'bucket', type: 'string', length: 255)]
    private $bucket;

    /**
     * @var string
     */
    #[ORM\Column(name: 'filename', type: 'string', length: 255)]
    private $filename;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255, nullable: true)]
    private $type;

    #[ORM\Column(name: 'onS3', type: 'boolean')]
    private $onS3;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $folder;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $version;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $ordered;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $label;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $sizePrefixes;

    #[ORM\Column(nullable: true)]
    private ?float $fileSize = null;


    public function __construct() {
        $this->onS3 = true;
    }

    public function __clone() {
        if($this->id) {
            $this->id = null;
        }
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set bucket.
     *
     * @param string $bucket
     *
     * @return Media
     */
    public function setBucket($bucket)
    {
        $this->bucket = $bucket;

        return $this;
    }

    /**
     * Get bucket.
     *
     * @return string
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return Media
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isOnS3(): bool
    {
        return $this->onS3;
    }

    /**
     * @param bool $onS3
     */
    public function setOnS3(bool $onS3): void
    {
        $this->onS3 = $onS3;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(?string $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getOrdered(): ?int
    {
        return $this->ordered;
    }

    public function setOrdered(?int $ordered): self
    {
        $this->ordered = $ordered;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getSizePrefixes(): Array
    {
        $sizePrefixes = json_decode($this->sizePrefixes, true);
        return is_null($sizePrefixes) ? [] : $sizePrefixes;
    }

    public function setSizePrefixes($sizePrefixes): self
    {
        $this->sizePrefixes = json_encode($sizePrefixes);
        return $this;
    }

    public function getFileSize(): ?float
    {
        return $this->fileSize;
    }

    public function setFileSize(?float $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }
}
