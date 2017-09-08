<?php

namespace Tests\Unit;

use Binocular\Action;
use Binocular\Event;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    public function test_events_can_be_added_to_store()
    {
        $event = new class implements Event {

            private $currentState = ['foo' => 'bar'];

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

        $store = new InMemoryStore;
        $store->add($event);
        $storedEvent = $store->current($event->getEntityId());

        $this->assertSame($event, $storedEvent);
        // check if new state was applied
        $this->assertEquals($storedEvent->getCurrentState(), ['foo' => 'baz']);
    }
}