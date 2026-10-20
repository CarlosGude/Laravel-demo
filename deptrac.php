<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\ClassNameRegexConfig;
use Deptrac\Deptrac\Contract\Config\Collector\DirectoryConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

return static function (DeptracConfig $config): void {
    $config
        ->paths('./app')
        ->layers(
            // Matches app/<AnyModule>/Domain/... so new modules are picked up automatically.
            $domain = Layer::withName('Domain')->collectors(
                DirectoryConfig::create('app/[^/]+/Domain/'),
            ),
            $application = Layer::withName('Application')->collectors(
                DirectoryConfig::create('app/[^/]+/Application/'),
            ),
            $infrastructure = Layer::withName('Infrastructure')->collectors(
                DirectoryConfig::create('app/[^/]+/Infrastructure/'),
            ),
            $providers = Layer::withName('Providers')->collectors(
                DirectoryConfig::create('app/Providers/'),
            ),
            $console = Layer::withName('Console')->collectors(
                DirectoryConfig::create('app/Console/'),
            ),
            $laravel = Layer::withName('Laravel')->collectors(
                ClassNameRegexConfig::create('#^Illuminate\\#'),
            ),
        )
        ->rulesets(
            // Domain must stay pure: no dependency on Application, Infrastructure or the framework.
            Ruleset::forLayer($domain),
            Ruleset::forLayer($application)->accesses($domain),
            Ruleset::forLayer($infrastructure)->accesses($domain, $application, $laravel),
            Ruleset::forLayer($providers)->accesses($domain, $application, $infrastructure, $laravel),
            Ruleset::forLayer($console)->accesses($domain, $application, $infrastructure, $laravel),
        )
    ;
};
