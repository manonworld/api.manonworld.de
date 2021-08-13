<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\AbstractEntity;
use App\Entity\User;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 * @ORM\Table(name="`book`")
 */
class Book extends AbstractEntity
{

    /**
     * @ORM\Column(type="string", length=13)
     * @Assert\Isbn
     * @Groups({"read", "write"})
     */
    private $isbn;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *      pattern="/^[a-zA-Z0-9 ]{4,255}$/",
     *      match=true,
     *      message="Title Must Be Alphanumeric"
     * )
     * @Groups({"read", "write"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *      pattern="/^[a-zA-Z0-9 ]{4,255}$/",
     *      match=true,
     *      message="Description Must Be Alphanumeric"
     * )
     * @Groups({"read", "write"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="books")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"system"})
     * @Ignore()
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url
     * @Groups({"read", "write"})
     */
    private $url;

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
