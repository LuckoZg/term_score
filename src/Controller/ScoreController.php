<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Term;
use App\Factory\ProviderFactory;

class ScoreController extends AbstractController
{
    private $term_positive = ' rocks';
    private $term_negative = ' sucks';
    private $multiplier = 10;
    private $db_score_expires = "+7 day";

    /**
     * Get popularity score of given term on external services (providers)
     * like GitHub or Twitter.
     * 
     * Example GET calls:
     *  - /api/v1/php/
     *  - /api/v1/php/twitter
     * 
     * GitHub is default provider, so you don't need to explicitly call:
     *  - /api/v1/php/github (but certainly you can do that)
     * 
     * @Route("/score/{term}/{provider}", name="score", methods={"GET"})
     */
    public function get_score(string $term, string $provider = 'github'): JsonResponse
    {
        // Validate if provider exists
        if(!$this->validate_provider($provider)){
            return new JsonResponse(
                $data=['status_message' => $this->getParameter('app.error_messages')['provider_not_available']], 
                $status=JsonResponse::HTTP_SERVICE_UNAVAILABLE
            );
        }

        // Get score from database or from external API
        $term = trim($term);
        $score = $this->get_score_from_database($term, $provider);
        if(!$score){
            $score = $this->get_score_from_provider($term, $provider);
            $this->set_score_to_database($term, $provider, $score);
        }

        // Return score to client
        return new JsonResponse(
            $data=['term' => $term, 'score' => $score], 
            $status=JsonResponse::HTTP_OK
        );
    }

    private function validate_provider($provider): bool
    {
        /**
         * Validate if provider is defined in ProviderFactory class as a constant
         * and if class exists in providers namespace.
         */
        if(!isset(ProviderFactory::providers[$provider]) || !class_exists(ProviderFactory::get_provider_classname($provider))){
            return false;
        }

        return true;
    }

    private function get_full_score($results): float
    {
        /**
         * Algorithm which returns popularity on scale 1-10 based on arrays 
         * of positive and negative sum.
         */
        $sum_count = $results['positive_count'] + $results['negative_count'];
        if($sum_count == 0){
            return round(($sum_count), 2);
        }

        $score = ($results['positive_count'] / $sum_count) * $this->multiplier;

        return round(($score), 2);
    }

    private function get_score_from_provider($term, $provider): float
    {
        /**
         * Get popularity score for given term from external service (like GitHub).
         */
        $client = HttpClient::create();
        $provider = ProviderFactory::get_provider_instance($provider);
        $results = $provider->get_results($client, $term, $this->term_positive, $this->term_negative);

        return $this->get_full_score($results);
    }

    private function get_score_from_database($term, $provider): ?float
    {
        /**
         * Get popularity score for given term and provider from database.
         * We use this method so we can query faster results with database layer using as cache.
         */
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->createQuery('
            SELECT term
            FROM App\Entity\Term term
            WHERE term.name = ?1
            AND term.provider = ?2
            AND term.expires > ?3
        ');

        $query->setParameter(1, $term);
        $query->setParameter(2, $provider);
        $query->setParameter(3, gmdate('Y-m-d H:i:s'));
        $term_from_db = $query->getOneOrNullResult();

        return $term_from_db ? $term_from_db->getScore() : NULL;
    }

    private function set_score_to_database($term, $provider, $score): void
    {
        /**
         * Post or update score for given term and provider in database. 
         */
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->createQuery('
            SELECT term
            FROM App\Entity\Term term
            WHERE term.name = ?1
            AND term.provider = ?2
        ');

        $query->setParameter(1, $term);
        $query->setParameter(2, $provider);
        $term_from_db = $query->getOneOrNullResult();

        if($term_from_db){
            $this->update_term($entityManager, $term_from_db, $score);
        } else {
            $this->create_term($entityManager, $term, $provider, $score);
        }
    }

    private function update_term($entityManager, $term_from_db, $score): void
    {
        /**
         * Update existing term with new score and expires (when cache is invalid) date.
         */
        $term_from_db->setScore($score);
        $term_from_db->setExpires(new \DateTime(gmdate('Y-m-d H:i:s', strtotime($this->db_score_expires))));
        $entityManager->flush();
    }

    private function create_term($entityManager, $term, $provider, $score): void
    {
        /**
         * Create new term with all parameters.
         */
        $new_term = new Term();
        $new_term->setName($term);
        $new_term->setProvider($provider);
        $new_term->setScore($score);
        $new_term->setExpires(new \DateTime(gmdate('Y-m-d H:i:s', strtotime($this->db_score_expires))));

        $entityManager->persist($new_term);
        $entityManager->flush();
    }
}