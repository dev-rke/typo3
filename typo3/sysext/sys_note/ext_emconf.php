<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 CMS System Notes',
    'description' => 'Records with messages which can be placed on any page and contain instructions or other information related to a page or section.',
    'category' => 'be',
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
