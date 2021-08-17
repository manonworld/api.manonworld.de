<?php

namespace App\Entity;

use App\Entity\Book;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use App\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends AbstractEntity implements UserInterface, PasswordAuthenticatedUserInterface
{

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"read", "write"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\Regex(
     *      pattern="/^[a-zA-Z0-9]{4,255}$/",
     *      match=true,
     *      message="Password Must Be Alphanumeric"
     * )
     * @Assert\NotBlank
     * @Assert\NotCompromisedPassword
     * @Groups({"write"})
     */
    private $password;

    /**
     * @var string The API token used for auth
     * @ORM\Column(type="string", unique=true)
     * @Groups("read", "write")
     */
    private $apiToken;

    /**
     * Mainly used to hash the password in the prePersist event
     * @Ignore()
     */
    private UserPasswordHasherInterface $hasher;

    /**
     * @ORM\OneToMany(targetEntity=Book::class, mappedBy="user", orphanRemoval=true)
     */
    private $books;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read", "write"})
     */
    private $image;

    /**
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher       = $hasher;
        $this->books        = new ArrayCollection;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getApiToken(): string|null
    {
        return $this->apiToken;
    }

    public function setApiToken(UuidInterface $token): self
    {
        $this->apiToken = $token;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     * @return string|null
     */
    public function getUserIdentifier(): ?string
    {
        return $this->apiToken;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     * @Groups("write")
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @Groups("write")
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function encryptPassword(): void
    {
        $this->password = $this->hasher->hashPassword($this, $this->password);
    }

    /**
     * @ORM\PrePersist
     */
    public function assignApiToken(): void
    {
        $this->apiToken = $this->hasher->hashPassword($this, Uuid::uuid4()->toString());
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @Ignore()
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Book[]
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            $book->setUser($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getUser() === $this) {
                $book->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function convertImageToBase64String(): void
    {
        if ( $this->image ) {
            $normalizer = new DataUriNormalizer;
            
            $this->image = $normalizer->normalize( $this->image );
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function convertImageToBase64StringIfNot(): void
    {
        if ( $this->image instanceof \SplFileObject ) {

            $normalizer = new DataUriNormalizer;

            $this->image = $normalizer->normalize( $this->image );
        }
    }

    public function getImage(): string|\SplFileObject|null
    {
        return $this->image;
    }

    public function setImage(string|\SplFileObject|null $image): self
    {
        $this->image = $image;

        return $this;
    }
}
