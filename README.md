# About:
Service which check popularity of given term on platforms like GitHub or Twitter.

# Install:

# Usage:

# Technology:
PHP 7.4.5
MySQL
Symfony 4.4

# Resource I Used:
https://symfony.com/doc/current/index.html
https://www.thinktocode.com/2018/03/26/symfony-4-rest-api-part-1-fosrestbundle/
https://www.adcisolutions.com/knowledge/getting-started-rest-api-symfony-4

# Suggestions:
- Instead of using local database for faster querying, we could use reverse proxy caching (time caching) for given term and provider.
Pros: 
- Don't need database layer which simplifies infrastructure and limit resource usage.
Cons: 
- Don't have access to data if external apis (providers) are down or if we need data for analytics.
