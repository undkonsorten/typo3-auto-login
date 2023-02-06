<?php

declare(strict_types=1);

use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create();
$config->getFinder()
    ->in(dirname(__DIR__, 2))
    ->ignoreVCSIgnored(true)
;

return $config;
