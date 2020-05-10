<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Term;

class ScoreController extends AbstractController
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
        if(!isset($this->providers[$provider]) || !class_exists($this->providers_namespace.$this->providers[$provider])){
            return new JsonResponse(
                $data=['status_message' => $this->error_messages['provider_not_available']], 
                $status=$this->status_codes['not_available']
            );
        }

        $score = $this->get_score_from_database($term, $provider);
        if(!$score){
            $score = $this->get_score_from_provider($term, $provider);
            $this->set_score_to_database($term, $provider, $score);
        }

        return new JsonResponse(
            $data=['term' => $term, 'score' => $score], 
            $status=$this->status_codes['ok']
        );
    }

    private function get_score_from_database($term, $provider)
    {
        $repository = $this->getDoctrine()->getRepository(Term::class);
        $term = $repository->findOneBy([
            'name' => $term,
            'provider' => $provider,
        ]);

        return $term ? $term->getScore() : NULL;
    }

    private function set_score_to_database($term, $provider, $score)
    {
        return;
    }

    private function get_score_from_provider($term, $provider): float
    {
        $client = HttpClient::create();
        $provider_class = $this->providers_namespace.$this->providers[$provider];
        $provider = new $provider_class;
        $results = $provider->get_results($client, $term, $this->term_positive, $this->term_negative);

        return $this->get_full_score($results);
    }

    private function get_full_score($results): float
    {
        $sum_count = $results['positive_count'] + $results['negative_count'];
        $score = ($results['positive_count'] / $sum_count) * $this->multiplier;

        return round(($score), 2);
    }
}