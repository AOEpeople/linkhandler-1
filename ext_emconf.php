<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Generic record link handler',
    'description' => 'Create links to any record (e.g. news)',
    'category' => 'plugin',
    'version' => '4.0.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'author' => 'Francois Suter',
    'author_email' => 'typo3@cobweb.ch',
    'author_company' => 'Cobweb Development Sarl',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.1-8.7.99',
        ],
        'conflicts' => [
            'ch_rterecords' => '',
            'tinymce_rte' => '',
        ],
        'suggests' => [],
    ],
];
