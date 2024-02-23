<?php

declare(strict_types=1);

use Remind\FormLog\Controller\LogModuleController;

return [
      'web_RmndFormLog' => [
        'parent' => 'web',
        'access' => 'user,group',
        'iconIdentifier' => 'module-form',
        'position' => ['after' => 'web_FormFormbuilder'],
        'labels' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_module.xlf',
        'extensionName' => 'FormLog',
        'controllerActions' => [
            LogModuleController::class => [
                'list', 'downloadCsv',
            ],
        ],
      ],
];
