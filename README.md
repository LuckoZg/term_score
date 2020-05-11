# About:
RESTful JSON API service which check popularity of given term on platforms like GitHub or Twitter. API
returns score on scale from 1 - 10 for given term and platform.

# Installation:
- Install PHP 7.4.5 and MySQL 5.7
> Most simpler solution is to install [XAMPP](https://www.apachefriends.org/download.html) or [LAMP](https://bitnami.com/stack/lamp/installer) (Linux) which comes with PHP and MySQL. Be careful to install proper version with PHP 7.4.5 and MySQL 5.7 version.
- Install [Composer Package Manager](https://getcomposer.org/download/)
- Install [Symfony](https://symfony.com/download) version 4.4.8
- Clone project
```sh
$ git clone https://github.com/LuckoZg/term_score.git .
```
- Go to root of project and run `composer install` to install all vendors (packages)
```sh
$ composer install
```
- Run command to create database and migrate table
> Be sure that your MySQL server is up and running.
1. Create database
 ```sh
$ php bin/console doctrine:database:create
```
2. and then migrate
 ```sh
$ php bin/console doctrine:migrations:migrate
```
- Run server and go live
```sh
$ symfony server:start
```

Server should be running on: 
http://localhost:8000/


# Usage Examples:
- To find out how popular is php on GitHub call:
    - `/api/v1/score/php/`
    or
    - `/api/v1/score/php/github`

> Note: GitHub is default provider, so you don't need to explicitly call it.

- You will get JSON response (200 OK) with this body structure:
```json
{
    "term": "php",
    "score": 3.32
}
```

- Another call examples:
    - `/api/v1/score/java/`
    - `/api/v1/score/php/twitter` *
    - `/api/v1/score/js/stackoverflow` *

- \* Twitter and Stackoverflow are not implemented yet, so you will get this response (503 Service Not Available):
```json
{
    "status_message": "Provider is not available."
}
```


- But you can implement providers easily following next section.

# How To Add New Provider:
- Open `src/Factory/ProviderFactory.php` and add the provider name and class name (which you will be building).
```php
    const providers = array(
        'github' => 'GitHubProvider',
        '<provider_name>' => '<provider_class>'
    );
```

- Create new file `src/Provider/<name_of_provider>Provider.php`
- Implement `src/Provider/Provider.php` abstract class which gives you rules of what attributes and methods you need to implement for your provider.
- After you finish a coding of class, you can query results on:
    `/api/v1/score/{term}/{new_provider}`

# Technology:
- PHP 7.4.5
- MySQL 5.7
- Symfony 4.4.8

# Suggestions:
- Instead of using local database for faster querying, we could use reverse proxy caching (time caching) for given term and provider.
  - Pros: 
    - Don't need database layer which simplifies infrastructure and limit resource usage.
  - Cons: 
    - Don't have access to data if external apis (providers) are down or if we need data for analytics.

# Possible Upgrade:
- Build more functional (integration) and unit tests so service would be more reliable and robust.
- Build CI/CD.
- Implement Throttling on API endpoint.
- Create two more tables in SQL for terms and providers, and set relations with main table (if we keep database layer).
- Implement Exception subclass which returns JSON response on Exception.
- Implement Logger.
- Dockerize.

# Timeline Of Building Project:
- Day 1: Get familiar with Symfony framework (3-5 hours)
- Day 2: Get familiar with Symfony framework and play around (3-5 hours)
- Day 3: Start building our project (basic API endpoint) (10-12 hours)
- Day 4: Refactor API endpoint and add database layer (10-12 hours)
- Day 5: Refactor, add README.md and comments (5-7 hours)
> Time of building: **30-40 hours**.