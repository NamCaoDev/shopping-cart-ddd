<?php

namespace App\Configuration;

use Ecotone\Dbal\Configuration\DbalConfiguration;
use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\SymfonyBundle\Config\SymfonyConnectionReference;
use Ecotone\SymfonyBundle\Messenger\SymfonyMessengerMessageChannelBuilder;

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
    public function asyncMessageChannel(): SymfonyMessengerMessageChannelBuilder
    {
        // Backed by the Kafka-backed Symfony Messenger transport of the same name
        return SymfonyMessengerMessageChannelBuilder::create('async_email_queue');
    }
}
