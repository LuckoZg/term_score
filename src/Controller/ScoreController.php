<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;

class ScoreController
{
    private $term_positive = ' rocks';
    private $term_negative = ' sucks';
    private $multiplier = 10;

    private $providers_namespace = 'App\Provider\\';
    private $providers = array(
        'github' => 'GitHubProvider'
    );

    private $error_messages = array(
        'provider_not_available' => 'Provider is not available.'
    );

    private $status_codes = array(
        'ok' => 200,
        'not_available' => 503
    );

    /**
     * @Route("/score/{term}/{provider}", name="score", methods={"GET"})
     */
    public function get_score(string $term, string $provider = 'github'): JsonResponse
    {
        // Check if provider and provider class is defined
        if(!isset($this->providers[$provider]) || !class_exists($this->providers_namespace.$this->providers[$provider])){
            return new JsonResponse(
                $data=['status_message' => $this->error_messages['provider_not_available']], 
                $status=$this->status_codes['not_available']
            );
        }

        // Check if term score for provider is already saved into database

        // Fetch data from provider
        $results = $this->get_results_from_provider($term, $provider);
        $score = $this->get_full_score($results);

        // Post/Update data to database in another thread (async)

        return new JsonResponse(
            $data=['term' => $term, 'score' => $score], 
            $status=$this->status_codes['ok']
        );
    }

    private function get_results_from_provider($term, $provider): array
    {
        $client = HttpClient::create();
        $provider_class = $this->providers_namespace.$this->providers[$provider];
        $provider = new $provider_class;
        
        return $provider->get_results($client, $term, $this->term_positive, $this->term_negative);
    }

    private function get_full_score($results): float
    {
        $sum_count = $results['positive_count'] + $results['negative_count'];
        $score = ($results['positive_count'] / $sum_count) * $this->multiplier;

        return round(($score), 2);
    }
}