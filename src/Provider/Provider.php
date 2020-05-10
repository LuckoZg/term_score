<?php

namespace App\Provider;

abstract class Provider
{
    private $url;

    abstract public function get_results($client, $term, $term_positive='', $term_negative='');
}