<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;

class ScoreController
{
    private $term_positive = ' rocks';
    private $term_negative = ' sucks';
    private $factor = 10;

    private $providers_namespace = 'App\Provider\\';
    private $providers = array(
        'github' => 'GitHubProvider',
        'twitter' => 'TwitterProvider'
    );

    private $error_messages = array(
        'provider_not_available' => 'Provider is not available.',
        'provider_not_implemented' => 'Provider is not implemented.'
    );

    /**
     * @Route("/score/{term}/{provider}", name="score", methods={"GET"})
     */
    public function get_score(string $term, string $provider = 'github')
    {
        // Check if provider and provider class is defined
        if(!isset($this->providers[$provider]) || !class_exists($this->providers_namespace.$this->providers[$provider])){
            return new JsonResponse(['status_message' => $this->error_messages['provider_not_available']], $status=500);
        }

        // Check if term score for provider is already saved into database

        // Fetch data from provider
            $client = HttpClient::create();

            $provider_class = $this->providers_namespace.$this->providers[$provider];
            $provider = new $provider_class;

            try {
                $results = $provider->get_results($client, $term, $this->term_positive, $this->term_negative);
            } catch (\Throwable $th) {
                return new JsonResponse(['status_message' => $this->error_messages['provider_not_implemented']], $status=500);
            }

            $score = $this->get_full_score($results);

            // Post/Update data to database in another thread (async)

        return new JsonResponse(['term' => $term, 'score' => $score], $status=200);
    }

    private function get_full_score($results){
        $sum_count = $results['positive_count'] + $results['negative_count'];
        $score = ($results['positive_count'] / $sum_count) * $this->factor;

        return round(($score), 2);
    }
}