<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

return Finder::create()
    ->files()
    ->in(dirname(__DIR__, 2))
    ->ignoreVCSIgnored(true)
;
