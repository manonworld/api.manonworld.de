<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RegisterController extends AbstractController
{
    
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private Request $request;
    private UserRepository $repo;
    protected $container;
    private array $denorm;

    public function __construct(
        ValidatorInterface $validator, 
        SerializerInterface $serializer,
        Request $request,
        UserRepository $repo,
        ContainerInterface $container
    ){
        $this->serializer   = $serializer;
        $this->validator    = $validator;
        $this->request      = $request;
        $this->repo         = $repo;
        $this->container    = $container;

        $this->setDenorm();
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(): JsonResponse
    {
        $data = $this->request->getContent();

        $this->denorm['groups'] = ['write'];

        $user = $this->serializer->deserialize($data, User::class, 'json', $this->denorm);

        $errors = $this->validator->validate($user);

        if ( count( $errors ) ) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $savedUser = $this->repo->save($user);
        } catch (\Exception) {
            return $this->json(['error' => 'user exists'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->denorm['groups'] = ['read'];

        $result = $this->serializer->serialize($savedUser, 'json', $this->denorm);

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