<?php

namespace Tests\Unit;

use Binocular\Action;
use Binocular\Event;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    public function test_events_can_be_added_to_store()
    {
        $event = $this->getEvent();

        $store = new InMemoryStore($this->getReducers());
        $store->dispatch($event);
        $state = $store->getState($event->getEntityId());

        // check if new state was applied
        $this->assertEquals($state, ['foo' => 'baz']);
    }

    private function getReducers()
    {
        return [
            'create' => [
                '1.0' => function (array $currentState, array $actionData): array {
                    // calculate new state
                    return $actionData;
                }
            ]
        ];
    }

    private function getEvent(): Event
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
}