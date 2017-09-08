<?php

namespace Tests\Unit;

use Binocular\Event;
use Binocular\Store;

class InMemoryStore implements Store
{
    /**
     * @var Event[]
     */
    private static $entities = [];

    public function add(Event $event)
    {
        $actions = $this->getActions();
        $action = $event->getAction();

        if (!isset($actions[$action->getName()][$action->getVersion()])) {
            throw new \RuntimeException(
                sprintf('Action %s version %s not found', $action->getName(), $action->getVersion())
            );
        }

        $version = $actions[$action->getName()][$action->getVersion()];

        if (!is_callable($version)) {
            throw new \RuntimeException(
                sprintf('Action %s version %s is not callable', $action->getName(), $action->getVersion())
            );
        }

        $this->persist($event, $version);
    }

    public function current(string $entityId): ?Event
    {
        if (isset(self::$entities[$entityId])) {
            return end(self::$entities[$entityId]);
        }

        return null;
    }

    private function persist(Event $event, Callable $actionVersion)
    {
        if (!isset(self::$entities[$event->getEntityId()])) {
            self::$entities[$event->getEntityId()] = [];
        }

        $version = count(self::$entities[$event->getEntityId()]);

        $newState = $actionVersion($event->getAction()->getData());

        $event->setCurrentState($newState);

        self::$entities[$event->getEntityId()][$version] = $event;
    }

    public function getActions(): array
    {
        return [
            'create' => [
                '1.0' => function (array $currentState): array {
                    return $currentState;
                }
            ]
        ];
    }
}