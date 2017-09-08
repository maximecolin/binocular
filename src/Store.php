<?php

namespace Binocular;

interface Store
{
    public function add(Event $event);

    public function current(string $entityId): ?Event;

    public function getActions(): array;
}