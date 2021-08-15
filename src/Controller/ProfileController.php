<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ProfileController extends AbstractController
{

    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/profile', name: 'get_profile', methods: ["GET"])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();

        $result = $this->serializer->serialize($user, 'json', ['groups' => ['read']]);

        return JsonResponse::fromJsonString($result);
    }
    
    #[Route("/profile", name: "update_profile", methods: ["PUT"])]
    public function update(): JsonResponse
    {
        echo 2;
        exit;
    }
}
