<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ScoreController
{
    /**
     * @Route("/score/{term}", name="score")
     */
    public function getScore(string $term)
    {
        return new JsonResponse(['term' => $term]);

        // the shortcut defines three optional arguments
        // return $this->json($data, $status = 200, $headers = [], $context = []);
    }
}