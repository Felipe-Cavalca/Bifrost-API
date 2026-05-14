<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('tmp')
    ->exclude('vendor')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        'encoding' => true,
        'full_opening_tag' => true,
        'single_blank_line_at_eof' => true,
    ])
    ->setFinder($finder);
