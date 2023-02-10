<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:entry',
        'label' => 'form_data',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/content/content-form.svg',
    ],
    'columns' => [
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'form_identifier' => [
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:form_identifier',
            'onChange' => 'reload',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'form_data' => [
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:form_data',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
        'finisher_data' => [
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:finisher_data',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
    ],
    'types' => [
        0 => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    form_identifier,
                    form_data,
                    finisher_data,
            ',
        ],
    ],
];
