<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\UserUpdater;
use App\Exception\ValidationException;

class ProfileController extends AbstractController
{

    private SerializerInterface $serializer;
    private UserUpdater $updater;

    public function __construct(SerializerInterface $serializer, UserUpdater $updater)
    {
        $this->serializer   = $serializer;
        $this->updater      = $updater;
    }

    #[Route('/profile', name: 'get_profile', methods: ["GET"])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();

        $result = $this->serializer->serialize($user, 'json', ['groups' => ['read']]);

        return JsonResponse::fromJsonString( $result );
    }
    
    #[Route("/profile", name: "update_profile", methods: ["PUT"])]
    public function update(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $user = $this->getUser();

        try {
            $user = $this->updater->update( $content, $user );
        } catch ( ValidationException $e ) {
            return $this->json($e->getViolations(), $e->getCode());
        } catch ( NotNormalizableValueException ) {
            return $this->json(['error' => 'Invalid Image'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $result = $this->serializer->serialize($user, 'json', ['groups' => ['read']]);

        return JsonResponse::fromJsonString( $result, Response::HTTP_ACCEPTED );
    }
}
