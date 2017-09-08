<?php

namespace Binocular;

interface Store
{
    public function dispatch(Event $event): ?array;

    public function getState(string $entityId): ?array;
}