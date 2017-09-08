<?php

namespace Binocular;

interface Event
{
    public function getEntityId(): string;

    public function getAction(): Action;

    public function getCurrentState(): array;

    /**
     * Metadata is for debugging/audit purposes.
     * Should only be saved and never and not interact with actions.
     */
    public function getMetadata(): array;
}