<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'AWS Image Rekognition',
    'description' => 'Add file metadata via AWS Rekognition',
    'category' => 'plugin',
    'author' => 'Guido Schmechel',
    'author_email' => 'info@ayacoo.de',
    'state' => 'stable',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.3.99',
            'typo3' => '13.0.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Ayacoo\\AwsMeta\\' => 'Classes/',
        ],
    ],
];
