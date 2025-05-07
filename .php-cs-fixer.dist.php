<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->ignoreDotFiles(true)
    ->ignoreVCSIgnored(true)
    ->in(__DIR__)
;

return (new Config())
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        'no_unused_imports' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
