<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ScoreController
{
    /**
     * @Route("/score/{term}/{provider}", name="score")
     */
    public function getScore(string $term, string $provider = "github")
    {
        return new JsonResponse(['term' => $term, 'provider' => $provider]);

        // the shortcut defines three optional arguments
        // return $this->json($data, $status = 200, $headers = [], $context = []);
    }
}