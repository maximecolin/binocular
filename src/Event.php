<?php

namespace Binocular;

interface Event
{
    public function getEntityId(): string;

    public function getAction(): Action;

    public function getCurrentState(): array;
}