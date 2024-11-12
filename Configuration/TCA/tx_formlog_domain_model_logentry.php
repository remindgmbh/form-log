<?php

declare(strict_types=1);

return [
    'columns' => [
        'additional_data' => [
            'config' => [
                'readOnly' => true,
                'type' => 'text',
            ],
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:additional_data',
        ],
        'crdate' => [
            'config' => [
                'type' => 'passthrough',
            ],
            'label' => 'crdate',
        ],
        'form_data' => [
            'config' => [
                'readOnly' => true,
                'type' => 'text',
            ],
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:form_data',
        ],
        'form_identifier' => [
            'config' => [
                'readOnly' => true,
                'type' => 'input',
            ],
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:form_identifier',
            'onChange' => 'reload',
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
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/content/content-form.svg',
        'label' => 'form_data',
        'origUid' => 't3_origuid',
        'sortby' => 'sorting',
        'title' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:entry',
        'tstamp' => 'tstamp',
    ],
    'types' => [
        0 => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    form_identifier,
                    form_data,
                    additional_data,
            ',
        ],
    ],
];
