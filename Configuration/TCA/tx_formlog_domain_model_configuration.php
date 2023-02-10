<?php
use Remind\FormLog\Backend\ItemsProc;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:configuration',
        'label' => 'form_identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/module/module-config.svg',
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
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => ItemsProc::class . '->getFormIdentifiers',
            ],
        ],
        'header_elements' => [
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:header_elements',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => ItemsProc::class . '->getFormElements',
            ],
        ],
        'finisher_options' => [
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:finisher_options',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => ItemsProc::class . '->getFinisherOptions',
            ],
        ],
        'items_per_page' => [
            'label' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_tca.xlf:items_per_page',
            'config' => [
                'type' => 'input',
                'eval' => 'int',
                'default' => 25,
            ],
        ],
    ],
    'types' => [
        0 => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    form_identifier,
                    header_elements,
                    finisher_options,
                    items_per_page,
            ',
        ],
    ],
];
