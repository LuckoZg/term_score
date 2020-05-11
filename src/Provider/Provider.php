<?php

namespace App\Provider;

abstract class Provider
{
    // API url from provider
    private $url;

    /**
     * Here comes logic for getting data from external API services.
     */
    abstract public function get_results($client, $term, $term_positive='', $term_negative=''): array;
}