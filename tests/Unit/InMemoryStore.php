<?php

namespace Tests\Unit;

use Binocular\DeletedEntityException;
use Binocular\Event;
use Binocular\Store;

class InMemoryStore implements Store
{
    /**
     * @var Event[]
     */
    public static $entities = [];

    /**
     * @var array
     */
    private $reducers = [];

    public function __construct(array $reducers)
    {
        $this->reducers = $reducers;
    }

    public function dispatch(Event $event): ?array
    {
        $action = $event->getAction();

        if (!isset($this->reducers[$action->getName()][$action->getVersion()])) {
            throw new \RuntimeException(
                sprintf('Action %s version %s not found', $action->getName(), $action->getVersion())
            );
        }

        $reducer = $this->reducers[$action->getName()][$action->getVersion()];

        if (!is_callable($reducer)) {
            throw new \RuntimeException(
                sprintf('Action %s version %s is not callable', $action->getName(), $action->getVersion())
            );
        }

        $currentState = $this->getState($event->getEntityId());

        $newState = $reducer(is_null($currentState) ? [] : $currentState, $action->getData());

        $this->persist($event->getEntityId(), $newState, $action->toArray());

        return $newState;
    }

    public function getState(string $entityId): ?array
    {
        if (isset(self::$entities[$entityId])) {
            $currentState = end(self::$entities[$entityId]);

            if(!is_null($currentState['deleted_at'])) {
                $message = sprintf('Entity was deleted on %s', $currentState['deleted_at']->format(\DATE_W3C));
                throw new DeletedEntityException($message);
            }

            return $currentState['current_state'];
        }

        return null;
    }

    private function persist(string $entityId, array $currentState = null, array $action)
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