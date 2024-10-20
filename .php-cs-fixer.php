<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

return (new PhpCsFixer\Config())
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'indentation_type' => true,
        'array_syntax' => ['syntax' => 'short'],
        'global_namespace_import' => [
            "import_classes" => true,
            "import_constants" => false,
            "import_functions" => false,
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'cast_spaces' => [
            'space' => 'none',
        ],
        'ordered_class_elements' => [
            'sort_algorithm' => 'none'
        ],
        'no_unused_imports' => true,
        'class_attributes_separation' => [
            'elements' => [
                'property' => 'none',
                'trait_import' => 'none',
                'const' => 'none',
            ],
        ],
        'types_spaces' => [
            'space' => 'single',
        ],
        'protected_to_private' => true,
        'single_line_throw' => false,
        'single_line_after_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'yoda_style' => false,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
        ],
        'phpdoc_single_line_var_spacing' => true,
        'no_superfluous_phpdoc_tags' => false,
        'phpdoc_to_comment' => [
            'ignored_tags' => [
                'var',
            ],
        ],
        'no_empty_comment' => true,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'continue',
                'do',
                'if',
                'switch',
                'try',
                'yield',
                'for',
                'foreach',
                'while',
            ],
        ],
        \PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer::name() => true,
    ])
    ->setIndent("    ")
    ->setLineEnding("\n")
    ->setFinder($finder);
