<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('config')
    ->exclude('var')
    ->exclude('public/bundles')
    ->exclude('public/build')
    ->exclude('fea')
    ->notPath('bin/console')
    ->notPath('public/index.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@Symfony' => true,
        '@PHP83Migration' => true,
        '@DoctrineAnnotation' => true,
        'declare_strict_types' => true,
        'mb_str_functions' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'php_unit_strict' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'modernize_strpos' => true,
        'set_type_to_cast' => true,
        'array_push' => true,
        'modernize_types_casting' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'array_indentation' => true,
        'list_syntax' => true,
        'no_spaces_inside_parenthesis' => true,
        'return_to_yield_from' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'single_line_empty_body' => true,
        'fully_qualified_strict_types' => [
            'import_symbols' => true,
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile('.php-cs-fixer.cache');
