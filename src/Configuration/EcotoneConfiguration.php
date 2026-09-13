<?php

namespace App\Configuration;

use Ecotone\Dbal\Configuration\DbalConfiguration;
use Ecotone\Dbal\DbalBackedMessageChannelBuilder;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\SymfonyBundle\Config\SymfonyConnectionReference;

class EcotoneConfiguration
{
    #[ServiceContext]
    public function getDbalConfiguration(): DbalConfiguration
    {
        return DbalConfiguration::createWithDefaults()
            ->withDoctrineORMRepositories(true)
            ->withDeduplication(false);
    }

    #[ServiceContext]
    public function getConnectionReference(): SymfonyConnectionReference
    {
        // It tells Ecotone to use the Doctrine Manager Registry.
        return SymfonyConnectionReference::defaultManagerRegistry('default');
    }

    #[ServiceContext]
    public function asyncMessageChannel(): DbalBackedMessageChannelBuilder
    {
        // This tells Ecotone to use your database as a message queue
        return DbalBackedMessageChannelBuilder::create('async_email_queue')->withAutoDeclare(false);
    }
}
