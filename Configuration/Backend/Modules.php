<?php

declare(strict_types=1);

use Remind\FormLog\Controller\LogModuleController;

return [
      'web_RmndFormLog' => [
        'access' => 'user,group',
        'controllerActions' => [
            LogModuleController::class => [
                'list', 'downloadCsv',
            ],
        ],
        'extensionName' => 'FormLog',
        'iconIdentifier' => 'module-form',
        'labels' => 'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_module.xlf',
        'parent' => 'web',
        'position' => ['after' => 'web_FormFormbuilder'],
      ],
];
