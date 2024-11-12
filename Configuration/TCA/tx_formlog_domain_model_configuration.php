<?php

declare(strict_types=1);

use Remind\FormLog\Backend\ItemsProc;

return [
    'columns' => [
        'crdate' => [
            'config' => [
                'type' => 'passthrough',
            ],
            'label' => 'crdate',
        ],
        'form_identifier' => [
            'config' => [
                'itemsProcFunc' => ItemsProc::class . '->getFormIdentifiers',
                'renderType' => 'selectSingle',
                'type' => 'select',
            ],
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:form_identifier',
        ],
        'header_elements' => [
            'config' => [
                'itemsProcFunc' => ItemsProc::class . '->getFormElements',
                'renderType' => 'selectMultipleSideBySide',
                'type' => 'select',
            ],
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:header_elements',
        ],
        'items_per_page' => [
            'config' => [
                'default' => 25,
                'eval' => 'int',
                'type' => 'input',
            ],
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:items_per_page',
        ],
        'tstamp' => [
            'config' => [
                'type' => 'passthrough',
            ],
            'label' => 'tstamp',
        ],
    ],
    'ctrl' => [
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/module/module-config.svg',
        'label' => 'form_identifier',
        'origUid' => 't3_origuid',
        'sortby' => 'sorting',
        'title' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:configuration',
        'tstamp' => 'tstamp',
    ],
    'types' => [
        0 => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    form_identifier,
                    header_elements,
                    items_per_page,
            ',
        ],
    ],
];
