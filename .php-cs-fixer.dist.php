<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/app')
    ->in(__DIR__ . '/config')
    ->in(__DIR__ . '/routes')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                 => true,
        '@PHP83Migration'        => true,
        'array_syntax'           => ['syntax' => 'short'],
        'declare_strict_types'   => true,
        'no_unused_imports'      => true,
        'ordered_imports'        => ['sort_algorithm' => 'alpha'],
        'single_quote'           => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space'           => ['spacing' => 'one'],
    ])
    ->setFinder($finder);
