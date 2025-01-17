<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in('bundle')
    ->in('tests')
    ->files()->name('*.php');

$config = new PhpCsFixer\Config();
$config->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
    '@PSR12' => true,
    'array_syntax' => [
        'syntax' => 'short',
    ],
    'declare_strict_types' => true,
    'constant_case' => true,
    'combine_consecutive_unsets' => true,
    'native_function_invocation' => [
        'include' => [
            '@compiler_optimized',
        ],
    ],
    'no_extra_blank_lines' => [
        'tokens' => [
            'break',
            'continue',
            'extra',
            'return',
            'throw',
            'use',
            'parenthesis_brace_block',
            'square_brace_block',
            'curly_brace_block',
        ],
    ],
    'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => false],
    'operator_linebreak' => ['position' => 'beginning'],
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'yoda_style' => [
        'equal' => false,
        'identical' => false,
        'less_and_greater' => false,
        'always_move_variable' => false,
    ],
])
    ->setRiskyAllowed(true)
    ->setFinder(
        $finder
    );

return $config;
