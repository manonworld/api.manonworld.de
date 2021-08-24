<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\UserCreator;
use App\Exception\ValidationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class RegisterController extends AbstractController
{

    private Request $request;
    protected $container;
    private UserCreator $creator;
    private array $denorm;

    public function __construct(
        Request $request, 
        ContainerInterface $container,
        UserCreator $creator
    ){
        $this->request      = $request;
        $this->container    = $container;
        $this->creator      = $creator;

        $this->setDenorm();
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(): JsonResponse
    {
        $data = $this->request->getContent();
        
        try {
            $result = $this->creator->create($this->denorm, $data);
        } catch (ValidationException $e) {
            return $this->json($e->getViolations(), $e->getCode());
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'USER_EXISTS'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotEncodableValueException) {
            return $this->json(['error' => 'INVALID_REQUEST'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->json(['error' => 'SERVER_ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return JsonResponse::fromJsonString($result, Response::HTTP_CREATED);
    }

    /**
     * Sets the denormalizer context array to pass default constructor values to User entity
     * 
     * @return void
     */
    private function setDenorm(): void
    {
        $hasher = UserPasswordHasherInterface::class;

        $this->denorm = [
            AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS => [
                User::class => [
                    'hasher' => $this->container->get($hasher)
                ],
            ]
        ];
    }

}