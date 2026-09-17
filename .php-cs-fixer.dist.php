<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        'braces_position' => [
            'functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'same_line', 
        ],
    ])
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
    )
;
