<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        getcwd() . '/src',
        getcwd() . '/tests',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        Typo3SetList::TYPO3_11,
    ]);
};
