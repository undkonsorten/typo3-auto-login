<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$rootPath = dirname(__DIR__, 2);

/** @var ClassLoader $loader */
$loader = require $rootPath . '/.build/vendor/autoload.php';
$loader->register();

$configuration = new Configuration();
$configuration->ignoreErrorsOnPackage('phpunit/phpunit', [ErrorType::SHADOW_DEPENDENCY]);

return $configuration;
