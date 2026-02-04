<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;

/** @var ClassLoader $classLoader */
$classLoader = require \dirname(__DIR__, 2) . '/.build/vendor/autoload.php';

// Build service container
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
