<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/", name="welcome", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $data = ['status' => 'OK', 'message' => 'welcome'];

        return $this->json($data, Response::HTTP_OK);
    }
}