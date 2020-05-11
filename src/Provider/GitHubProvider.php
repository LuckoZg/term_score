<?php

namespace App\Provider;

use Symfony\Component\Config\Definition\Exception\Exception;
use App\Provider\Provider;

class GitHubProvider extends Provider
{
    private $url = 'https://api.github.com/search/issues?q=';

    public function get_results($client, $term, $term_positive='', $term_negative=''): array
    {
        // Concate urls
        $api_url_positive = $this->url.$term.$term_positive;
        $api_url_negative = $this->url.$term.$term_negative;

        try {
            // Get positive count for term
            $response = $client->request('GET', $api_url_positive);
            $positive_count = $response->toArray()['total_count'];

            // Get negative count for term
            $response = $client->request('GET', $api_url_negative);
            $negative_count = $response->toArray()['total_count'];
        } catch(\Throwable $th){
            // We should send json response with proper message (Override Exception response for example).
            throw new Exception('External API Error: '.$th->getMessage());
        }

        return array('positive_count' => $positive_count, 'negative_count' => $negative_count);
    }
}