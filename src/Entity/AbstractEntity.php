<?php declare(strict_types=1);

namespace App\Entity;

use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class AbstractEntity
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="string", unique=true)
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @Assert\Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Ignore()
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Ignore()
     */
    private $updated_at;

    /**
     * @ORM\Column(type="boolean")
     * @Ignore()
     */
    private $is_deleted = false;

    /**
     * @Ignore()
     */
    private $dateFormat = 'Y-m-d\TH:i:s\Z';


    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @Ignore()
     */
    public function getCreatedAt(): ?string
    {
        return $this->created_at->format($this->dateFormat);
    }

    /**
     * @ORM\PrePersist
     * @Ignore()
     */
    public function setCreatedAt(): self
    {
        $this->created_at = new \DateTimeImmutable;

        return $this;
    }

    /**
     * @Ignore()
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at?->format($this->dateFormat);
    }

    /**
     * @ORM\PreUpdate
     * @Ignore()
     */
    public function setUpdatedAt(): self
    {
        $this->updated_at = new \DateTimeImmutable;

        return $this;
    }

    /**
     * @Ignore()
     */
    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    /**
     * @Ignore()
     */
    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }
}
