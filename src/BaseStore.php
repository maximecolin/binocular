<?php

namespace Binocular;

abstract class BaseStore
{
    /**
     * @var array
     */
    protected $reducers = [];

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

        return $reducer(is_null($currentState) ? [] : $currentState, $action->getData());
    }

    /**
     * If the entity is deletable this method should be used to check it.
     * If not, don't use it.
     */
    protected function checkState(array $rawEntity = null): ?array
    {
        if (is_null($rawEntity)) {
            return null;
        }

        if (!is_null($rawEntity['deleted_at'])) {
            $deletedAt = $rawEntity['deleted_at'];
            $date = $deletedAt instanceof \DateTime ? $deletedAt->format(\DATE_COOKIE) : json_encode($deletedAt);
            $message = sprintf('Entity was deleted on %s', $date);
            throw new DeletedEntityException($message);
        }

        return $rawEntity['current_state'];
    }
}