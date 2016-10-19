<?php
$buildConfig = [
    'major'                   => 2,
    'minor'                   => 9,
    'build'                   => 2,
    'shoppingsystem_id'       => 403,
    'shopgate_library_path'   => '',
    'shopgate_library_folder' => '',
    'plugin_name'             => 'magento2-import',
    'display_name'            => 'Shopgate M2 Import',
    'zip_filename'            => 'sg-m2-import.zip',
    'version_files'           => [
        '0' => [
            'path'    => '/etc/module.xml',
            'match'   => '#setup_version="(.*)"#',
            'replace' => 'setup_version="{PLUGIN_VERSION}"',
        ],
        '1' => [
            'path'    => 'composer.json',
            'match'   => '#"version": "(.*)"#',
            'replace' => '"version": "{PLUGIN_VERSION}"',
        ],
    ],
    'zip_basedir'             => '',
    'exclude_files'           => [
        '0' => '.git',
        '1' => 'build_config.php',
        '2' => 'create _tag.xml',
        '3' => 'create_zip.xml',
        '4' => 'build.properties',
    ],
    'wiki'                    => [
        'changelog' => [
            'path' => './',
        ],
    ],
    'is_adapter'              => 0,
];
