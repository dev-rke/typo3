<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 CMS Extension Manager',
    'description' => 'Backend module (Admin Tools>Extensions) for viewing and managing extensions.',
    'category' => 'module',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'TYPO3 Core Team',
    'author_email' => 'typo3cms@typo3.org',
    'author_company' => '',
    'version' => '12.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
