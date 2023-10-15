<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'aws_meta',
    'description' => 'Add file metadata via AWS Rekognition',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Ayacoo\\AwsMeta\\' => 'Classes/',
        ],
    ],
];
