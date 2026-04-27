<?php

use Maatwebsite\Excel\Excel;

return [
    'exports' => [
        'chunk_size'        => 1000,
        'pre_calculate_formulas' => false,
        'strict_null_comparison' => false,
        'csv'               => ['use_bom' => false, 'include_separator_line' => false, 'excel_compatibility' => false, 'output_encoding' => '', 'test_auto_filter_by_a1' => true],
        'properties'        => ['creator' => 'POS Toko Waris', 'lastModifiedBy' => '', 'title' => '', 'description' => '', 'subject' => '', 'keywords' => '', 'category' => '', 'manager' => '', 'company' => 'Toko Waris'],
    ],
    'imports' => [
        'read_only' => true, 'ignore_empty' => false, 'heading_row' => ['formatter' => 'slug'],
        'csv' => ['delimiter' => ',', 'enclosure' => '"', 'escape_character' => '\\', 'contiguous' => false, 'input_encoding' => 'UTF-8'],
        'properties' => [],
    ],
    'extension_detector' => [
        'xlsx' => Excel::XLSX, 'xlsm' => Excel::XLSX, 'xltx' => Excel::XLSX, 'xltm' => Excel::XLSX,
        'xls'  => Excel::XLS,  'xlt'  => Excel::XLS,
        'ods'  => Excel::ODS,  'ots'  => Excel::ODS,
        'csv'  => Excel::CSV,  'tsv'  => Excel::TSV,
        'html' => Excel::HTML, 'htm'  => Excel::HTML,
        'pdf'  => Excel::DOMPDF,
    ],
    'value_binder' => ['default' => Maatwebsite\Excel\DefaultValueBinder::class],
    'cache' => ['driver' => 'memory', 'batch' => ['memory_limit' => 60000000]],
    'transactions' => ['handler' => 'db'],
    'temporary_files' => ['local_path' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-excel', 'remote_disk' => null, 'remote_prefix' => null, 'force_resync_remote' => null],
];
