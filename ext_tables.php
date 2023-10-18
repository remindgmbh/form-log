<?php

declare(strict_types=1);

defined('TYPO3_MODE') || die;

use Remind\FormLog\Controller\LogModuleController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerModule(
    'FormLog',
    'RmndBasemodulesRemind',
    'logmodule',
    '',
    [
        LogModuleController::class => 'list,downloadCsv',
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:form/Resources/Public/Icons/module-form.svg',
        'labels' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_module.xlf',
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'inheritNavigationComponentFromMainModule' => false,
    ]
);
