# Binocular

[![CircleCI](https://circleci.com/gh/thiagomarini/binocular.svg?style=svg)](https://circleci.com/gh/thiagomarini/binocular) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Doing CQRS + Event Sourcing without building a spaceship.

Please check out [my post](https://medium.com/@marinithiago/doing-event-sourcing-without-building-a-spaceship-6dc3e7eac000) supporting the idea.

##### Why Binocular as project name?
Like CQRS, binocular vision happens when two separate images from two eyes are successfully combined into one image in the brain. CQRS has two eyes: the read and write eyes.

##### Usage in a nutshell

```
composer require thiagomarini/binocular
```

```php
use Binocular\Action;
use Binocular\Event;
use Binocular\Store;

$reducers = [
    'create' => [
        '1.0' => function (array $currentState, array $actionData): ?array {
            // calculate new state
            return $actionData;
        }
    ]
];

$store = new Store($reducers);

$updateEvent = new Event();
$store->dispatch($updateEvent);
$state = $store->getState($updateEvent->getEntityId());
```