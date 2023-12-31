<?php

declare(strict_types=1);

namespace Someniatko\ResultTypePsalmPlugin;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        require_once __DIR__ . '/ResultAllTypeProvider.php';
        $registration->registerHooksFromClass(ResultAllTypeProvider::class);
    }
}
