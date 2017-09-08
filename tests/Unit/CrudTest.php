<?php

namespace Tests\Unit;

use Binocular\Action;
use Binocular\DeletedEntityException;
use Binocular\Event;
use Binocular\Store;
use PHPUnit\Framework\TestCase;

class CrudTest extends TestCase
{
    /**
     * @var Store
     */
    private $store;

    public function test_state_change()
    {
        $store = new InMemoryStore($this->getReducers());
        
        // create
        $createEvent = $this->getCreateEvent();
        $store->dispatch($createEvent);
        $state = $store->getState($createEvent->getEntityId());
        // check if new state is correct
        $this->assertEquals($state, ['foo' => 'baz']);
        $this->assertCount(1, InMemoryStore::$entities[$createEvent->getEntityId()]);

        // update
        $updateEvent = $this->getUpdateEvent();
        $store->dispatch($updateEvent);
        $state = $store->getState($updateEvent->getEntityId());
        // check if new state is correct
        $this->assertEquals($state, ['hello' => 'world', 'foo' => 'baz']);
        $this->assertCount(2, InMemoryStore::$entities[$updateEvent->getEntityId()]);

        // delete
        $deleteEvent = $this->getDeleteEvent();
        $store->dispatch($deleteEvent);
        try {
            $store->getState($deleteEvent->getEntityId());
        } catch (DeletedEntityException $e) {
            $this->assertCount(3, InMemoryStore::$entities[$deleteEvent->getEntityId()]);
        }
    }

    private function getReducers(): array
    {
        return [
            'create' => [
                '1.0' => function (array $currentState, array $actionData): ?array {
                    // calculate new state
                    return $actionData;
                }
            ],
            'update' => [
                '1.0' => function (array $currentState, array $actionData): ?array {
                    // calculate new state
                    return array_merge($currentState, $actionData);
                }
            ],
            'delete' => [
                '1.0' => function (array $currentState, array $actionData): ?array {
                    // calculate new state
                    return null; // null means delete (my decision)
                }
            ]
        ];
    }

    private function getCreateEvent(): Event
    {
        return new class implements Event
        {
            private $currentState = [];

            public function getEntityId(): string
            {
                return '1234Z';
            }

            public function getAction(): Action
            {
                return new Action('create', '1.0', ['foo' => 'baz']);
            }

            public function getCurrentState(): array
            {
                return $this->currentState;
            }

            public function setCurrentState(array $newState)
            {
                $this->currentState = $newState;
            }
        };
    }

    private function getUpdateEvent(): Event
    {
        return new class implements Event
        {
            private $currentState = [];

            public function getEntityId(): string
            {
                return '1234Z';
            }

            public function getAction(): Action
            {
                return new Action('update', '1.0', ['hello' => 'world']);
            }

            public function getCurrentState(): array
            {
                return $this->currentState;
            }

            public function setCurrentState(array $newState)
            {
                $this->currentState = $newState;
            }
        };
    }

    private function getDeleteEvent(): Event
    {
        return new class implements Event
        {
            private $currentState = [];

            public function getEntityId(): string
            {
                return '1234Z';
            }

            public function getAction(): Action
            {
                return new Action('delete', '1.0', []);
            }

            public function getCurrentState(): array
            {
                return $this->currentState;
            }

            public function setCurrentState(array $newState)
            {
                $this->currentState = $newState;
            }
        };
    }
}