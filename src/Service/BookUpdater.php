<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use App\Repository\BookRepository;
use App\Entity\Book;

class BookUpdater
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

    public function update(Book $book, string $data, User $user)
    {
        if ( $book->getUser() !== $user )
            throw new UnauthorizedException;
        
        $content = json_decode($data);

        if ( isset( $content->isbn ) )
            $book->setIsbn( $content->isbn );

        if ( isset( $content->title ) )
            $book->setTitle( $content->title );

        if ( isset( $content->description ) )
            $book->setDescription( $content->description );

        if ( isset( $content->url ) )
            $book->setUrl( $content->url );

        $errors = $this->validator->validate($book);

        if ( count( $errors ) )
            throw (new ValidationException)->setViolations($errors);

        $updatedBook = $this->repo->save($book);

        return $this->serializer->serialize($updatedBook, 'json');
    }
}