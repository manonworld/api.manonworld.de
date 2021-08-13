<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Exception\ValidationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class BookCreator
{
    private BookRepository $repo;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        BookRepository $repo, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator
    ){
        $this->repo         = $repo;
        $this->serializer   = $serializer;
        $this->validator    = $validator;
    }

    public function create(User $user, string $data)
    {
        $book = $this->serializer->deserialize($data, Book::class, 'json');
        $book->setUser($user);

        $errors = $this->validator->validate($book);
        if ( count($errors) ) {
            throw (new ValidationException)->setViolations($errors);
        }

        $createdBook = $this->repo->save($book);

        return $this->serializer->serialize($createdBook, 'json');
    }
}