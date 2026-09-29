<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->exclude(['vendor', 'var']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PER-CS2.0' => true,
        'declare_strict_types' => true,
        'phpdoc_align' => false,
        'single_line_throw' => false,
    ])
    ->setFinder($finder);
