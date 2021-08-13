<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ValidationException;
use App\Repository\BookRepository;
use App\Service\BookCreator;

class BookController extends AbstractController
{

    private BookRepository $repo;
    private BookCreator $creator;

    public function __construct(
        BookRepository $repo, 
        BookCreator $creator
    ){
        $this->repo     = $repo;
        $this->creator  = $creator;
    }

    /**
     * @Route("/books", name="books_list", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        $data = [
            'status'    => 'OK',
            'data'      => $this->repo->findAll()
        ];

        return $this->json($data);
    }

    /**
     * @Route("/books", name="create_book", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = $request->getContent();
        
        try {
            $result = $this->creator->create($user, $data);
        } catch (ValidationException $e) {
            return $this->json($e->getViolations(), $e->getCode());
        } catch (\Exception $e) {
            print_r($e->getFile() . $e->getLine() . $e->getMessage());
            exit;
            return $this->json(['error' => 'SERVER_ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return JsonResponse::fromJsonString($result, Response::HTTP_CREATED);
    }
    

}