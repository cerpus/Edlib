<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/app/')
    ->in(__DIR__ . '/bootstrap/')->exclude('cache')->name('/app.php')
    ->in(__DIR__ . '/config/')
    ->in(__DIR__ . '/database/')
    ->in(__DIR__ . '/routes/')
    ->in(__DIR__ . '/tests/')
;

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    // https://cs.symfony.com/doc/rules/index.html
    ->setRules([
        '@PER-CS2.0' => true,
        'array_push' => true,
        'native_function_casing' => true,
        'method_chaining_indentation' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_multiple_statements_per_line' => true,
        'no_short_bool_cast' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_unused_imports' => true,
        'phpdoc_no_package' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
    ])
    ->setRiskyAllowed(true)
;
