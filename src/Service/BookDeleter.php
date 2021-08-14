<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\BookRepository;
use App\Entity\Book;
use App\Entity\User;
use App\Exception\UnauthorizedException;

class BookDeleter
{

    private BookRepository $repo;

    public function __construct(BookRepository $repo)
    {
        $this->repo = $repo;
    }

    public function delete(Book $book, User $user): void
    {
        if ( $book->getUser() !== $user )
            throw new UnauthorizedException;

        $book->setIsDeleted(true);

        $this->repo->save($book);
    }
}