<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\BookRepository;
use App\Entity\Book;

class BookDeleter
{

    private BookRepository $repo;

    public function __construct(BookRepository $repo)
    {
        $this->repo = $repo;
    }

    public function delete(Book $book): void
    {
        $book->setIsDeleted(true);

        $this->repo->save($book);
    }
}