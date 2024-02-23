<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

(function () {
    ExtensionManagementUtility::addTypoScriptSetup(
        "@import 'EXT:rmnd_form_log/Configuration/TypoScript/setup.typoscript'"
    );
})();
