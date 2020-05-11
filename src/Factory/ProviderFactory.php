<?php

namespace App\Factory;

use App\Provider\Provider;

class ProviderFactory
{
    const providers_namespace = 'App\Provider\\';
    const providers = array(
        'github' => 'GitHubProvider'
    );

    public static function get_provider_instance($provider): Provider
    {
        $provider_class = self::get_provider_classname($provider);
        
        return new $provider_class;
    }

    public static function get_provider_classname($provider): string
    {
        return self::providers_namespace.self::providers[$provider];
    }
}