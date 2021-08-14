<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use App\Repository\BookRepository;
use App\Service\BookCreator;
use App\Service\BookDeleter;
use App\Service\BookUpdater;
use App\Entity\Book;

class BookController extends AbstractController
{

    private BookRepository $repo;
    private BookCreator $creator;
    private BookDeleter $deleter;
    private BookUpdater $updater;
    private SerializerInterface $serializer;

    public function __construct(
        BookRepository $repo, 
        BookCreator $creator,
        BookDeleter $deleter,
        BookUpdater $updater,
        SerializerInterface $serializer
    ){
        $this->repo         = $repo;
        $this->creator      = $creator;
        $this->deleter      = $deleter;
        $this->updater      = $updater;
        $this->serializer   = $serializer;
    }

    /**
     * @Route("/books", name="books_list", methods={"GET"})
     */
    public function all(): JsonResponse
    {
        $data = [
            'status'    => 'OK',
            'data'      => $this->repo->findBy(['is_deleted' => false])
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
            return $this->json(['error' => 'SERVER_ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return JsonResponse::fromJsonString($result, Response::HTTP_CREATED);
    }

    /**
     * @Route("/books/{id}", name="get_book", methods={"GET"})
     */
    public function find(Book $book)
    {
        if( $book->getIsDeleted() === true ) {
            $data = ['status' => 'error', 'message' => 'GONE'];
            return new JsonResponse($data, Response::HTTP_GONE);
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($book, 'json')
        );
    }

    /**
     * @Route("/books/{id}", name="delete_book", methods={"DELETE"})
     */
    public function delete(Book $book)
    {
        $user = $this->getUser();
        
        try {
            $this->deleter->delete( $book, $user );
        } catch ( UnauthorizedException $e ) {
            return $this->json( ['error' => $e->getMessage()], $e->getCode() );
        }
        
        $data = ['status' => 'OK', 'message' => 'DELETED'];

        return new JsonResponse($data, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/books/{id}", name="update_book", methods={"PUT"})
     */
    public function update(Book $book, Request $request)
    {
        if ( $book->getIsDeleted() === true ) {
            $data = ['status' => 'error', 'message' => 'GONE'];
            return new JsonResponse($data, Response::HTTP_GONE);
        }
        
        try {
            $result = $this->updater->update($book, $request->getContent());
        } catch (ValidationException $e) {
            return $this->json($e->getViolations(), $e->getCode());
        } catch (UnauthorizedException $e) {
            return $this->json($e->getViolations(), $e->getCode());
        } catch (\Exception $e) {
            return $this->json(['error' => 'SERVER_ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return JsonResponse::fromJsonString($result, Response::HTTP_ACCEPTED);
    }
}