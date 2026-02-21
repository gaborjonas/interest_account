<?php


declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return new Config()
    ->setRiskyAllowed(true)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile('temp/.php-cs-fixer.cache')
    ->setRules([
        '@PER-CS3x0:risky' => true,
        'align_multiline_comment' => true,
        'array_push' => true,
        'blank_line_before_statement' => true,
        'class_keyword' => true,
        'class_keyword_remove' => true,
        'class_reference_name_casing' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_parentheses' => true,
        'declare_strict_types' => true,
        'dir_constant' => true,
        'fully_qualified_strict_types' => true,
        'function_to_constant' => true,
        'global_namespace_import' => true,
        'include' => true,
        'is_null' => true,
        'lambda_not_used_import' => true,
        'list_syntax' => true,
        'method_chaining_indentation' => true,
        'modernize_types_casting' => true,
        'multiline_comment_opening_closing' => true,
        'multiline_promoted_properties' => true,
        'native_constant_invocation' => true,
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_null_property_initialization' => true,
        'no_redundant_readonly_property' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_around_offset' => true,
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_trailing_whitespace_in_string' => false,
        'no_unreachable_default_argument_value' => false,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_nullsafe_operator' => true,
        'no_useless_return' => true,
        'nullable_type_declaration' => ['syntax' => 'question_mark'],
        'ordered_attributes' => true,
        'ordered_interfaces' => true,
        'ordered_types' => true,
        'php_unit_attributes' => true,
        'php_unit_construct' => true,
        'php_unit_data_provider_method_order' => true,
        'php_unit_data_provider_name' => true,
        'php_unit_data_provider_return_type' => true,
        'php_unit_data_provider_static' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_dedicate_assert_internal_type' => true,
        'php_unit_expectation' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_array_type' => true,
        'phpdoc_indent' => true,
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_tag_casing' => true,
        'return_assignment' => true,
        'single_quote' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setFinder(
        new Finder()
            ->in(['src', 'tests'])
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
    );
