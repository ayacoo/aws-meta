<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

$tca = [
    'columns' => [
        'aws_labels' => [
            'exclude' => true,
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'LLL:EXT:aws_meta/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.aws_labels',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 3,
            ],
        ],
        'aws_text' => [
            'exclude' => true,
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'LLL:EXT:aws_meta/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.aws_text',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 3,
            ],
        ],
        'aws_custom_labels' => [
            'exclude' => true,
            'l10n_mode' => 'prefixLangTitle',
            'label' => 'LLL:EXT:aws_meta/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.aws_custom_labels',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 3,
            ],
        ],
    ],
];

$GLOBALS['TCA']['sys_file_metadata'] = array_replace_recursive($GLOBALS['TCA']['sys_file_metadata'], $tca);

ExtensionManagementUtility::addTCAcolumns(
    'sys_file_metadata',
    $tca
);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_metadata',
    'aws_labels, aws_text, aws_custom_labels',
    '',
    ''
);
