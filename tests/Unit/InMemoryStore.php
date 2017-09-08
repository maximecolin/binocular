<?php

namespace Tests\Unit;

use Binocular\BaseStore;
use Binocular\Event;
use Binocular\Store;

class InMemoryStore extends BaseStore implements Store
{
    /**
     * @var Event[]
     */
    public static $entities = [];

    public function dispatch(Event $event): ?array
    {
        // call the parent, it uses the reducer to apply the action
        $newState = parent::dispatch($event);

        $this->persist($event->getEntityId(), $newState, $event->getAction()->toArray());

        return $newState;
    }

    public function getState(string $entityId): ?array
    {
        $currentState = $this->retrieve($entityId);
        // check if it's deleted before returning
        return $this->checkState($currentState);
    }

    public function retrieve(string $entityId): ?array
    {
        if (isset(self::$entities[$entityId])) {
            return end(self::$entities[$entityId]);
        }

        return null;
    }

    public function persist(string $entityId, array $currentState = null, array $action)
    {
        if (!isset(self::$entities[$entityId])) {
            self::$entities[$entityId] = [];
        }

        $version = count(self::$entities[$entityId]);

        self::$entities[$entityId][++$version] = [
            'current_state' => $currentState,
            'action'        => $action,
            'created_at'    => new \DateTime,
            'deleted_at'    => is_null($currentState) ? new \DateTime : null,
        ];
    }
}