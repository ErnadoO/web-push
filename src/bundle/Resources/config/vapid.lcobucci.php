<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use WebPush\VAPID\JWSProvider;
use WebPush\VAPID\LcobucciProvider;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()
        ->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set(JWSProvider::class)
        ->class(LcobucciProvider::class)
        ->args([param('webpush.vapid.lcobucci.public_key'), param('webpush.vapid.lcobucci.private_key')])
    ;
};
