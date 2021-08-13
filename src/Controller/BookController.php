<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BookRepository;

class BookController extends AbstractController
{

    private BookRepository $repo;

    public function __construct(BookRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @Route("/books", name="books_list", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        $data = [
            'status' => 'OK',
            'data' => $this->repo->findAll()
        ];

        return $this->json($data);
    }

    /**
     * @Route("/books", name="create_book", methods={"POST"})
     */
    public function create(Request $request)
    {
        //TODO implement book insertion
    }
    

}