<?php

declare(strict_types=1);

namespace Remind\FormLog\Backend;

use Psr\Http\Message\ServerRequestInterface;
use Remind\FormLog\Utility\FormUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface as ExtbaseConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManagerInterface as ExtFormConfigurationManagerInterface;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

class ItemsProc
{
    /**
     * @param mixed[] $params
     */
    public function getFormIdentifiers(array &$params): void
    {
        $formPersistenceManager = $this->getFormPersistenceManager();
        $formSettings = $this->getFormSettings();
        $forms = $formPersistenceManager->listForms($formSettings);
        $params['items'] = array_map(function (array $form) {
            return [$form['identifier'], $form['identifier']];
        }, $forms);
        $selectedValues = GeneralUtility::trimExplode(',', $params['row']['form_identifier'] ?? '', true);
        $this->getInvalidItems($selectedValues, $params['items']);
    }

    /**
     * @param mixed[] $params
     */
    public function getFormElements(array &$params): void
    {
        $formIdentifier = $params['row']['form_identifier'][0] ?? null;
        if ($formIdentifier) {
            $formPersistenceManager = $this->getFormPersistenceManager();
            $formSettings = $this->getFormSettings();
            $elements = FormUtility::getFormElements(
                $formPersistenceManager,
                $formSettings,
                $formIdentifier
            );
            $params['items'] = array_map(function (array $element) {
                return [$element['label'] ?? $element['identifier'], $element['identifier']];
            }, $elements);
        }
        $selectedValues = GeneralUtility::trimExplode(',', $params['row']['header_elements'], true);
        $this->getInvalidItems($selectedValues, $params['items']);
    }

    /**
     * @param mixed[] $selectedValues
     * @param mixed[] $items
     */
    private function getInvalidItems(array $selectedValues, array &$items): void
    {
        $availableValues = array_map(function (array $item) {
            return $item[1];
        }, $items);
        $noMatchingLabel = sprintf(
            '[ %s ]',
            LocalizationUtility::translate(
                'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.noMatchingValue'
            )
        );
        $invalidItems = array_diff($selectedValues, $availableValues);
        $invalidItems = array_map(function (string $field) use ($noMatchingLabel) {
            return [sprintf($noMatchingLabel, $field), $field];
        }, $invalidItems);
        $items = array_merge(
            $invalidItems,
            $items,
        );
    }

    private function getFormPersistenceManager(): FormPersistenceManagerInterface
    {
        return GeneralUtility::makeInstance(FormPersistenceManagerInterface::class);
    }

    /**
     * @return mixed[]
     */
    private function getFormSettings(): array
    {
        $extbaseConfigurationManager = GeneralUtility::makeInstance(ExtbaseConfigurationManagerInterface::class);
        $extbaseConfigurationManager->setRequest($this->getRequest());
        $typoScriptSettings = $extbaseConfigurationManager->getConfiguration(
            ExtbaseConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'form'
        );
        $extFormConfigurationManager = GeneralUtility::makeInstance(ExtFormConfigurationManagerInterface::class);
        $formSettings = $extFormConfigurationManager->getYamlConfiguration($typoScriptSettings, false);
        return $formSettings;
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
