<?php

namespace AdvancedLearning\ApiHelpers\Tests;

use AdvancedLearning\ApiHelpers\Consumer;

class ConsumerTest
{
    use Consumer;

    protected function getBaseUri(): string
    {
        return 'http://localhost';
    }

    protected function getClientId(): string
    {
        return 'clientid';
    }

    protected function getClientSecret(): string
    {
        return 'clientsecret';
    }
}