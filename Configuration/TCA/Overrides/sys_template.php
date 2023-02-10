<?php

defined('TYPO3') or die;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(function () {
    ExtensionManagementUtility::addStaticFile(
        'rmnd_form_log',
        'Configuration/TypoScript',
        'REMIND - Form Log Extension'
    );
})();
