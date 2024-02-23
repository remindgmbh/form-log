<?php

declare(strict_types=1);

namespace Remind\FormLog\Backend;

use Remind\FormLog\Utility\FormUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManager;
use TYPO3\CMS\Form\Mvc\Configuration\YamlSource;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManager;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;
use TYPO3\CMS\Form\Slot\FilePersistenceSlot;

class ItemsProc
{
    public function getFormIdentifiers(array &$params): void
    {
        $formPersistenceManager = $this->getFormPersistenceManager();
        $forms = $formPersistenceManager->listForms();
        $params['items'] = array_map(function (array $form) {
            return [$form['identifier'], $form['identifier']];
        }, $forms);
        $selectedValues = GeneralUtility::trimExplode(',', $params['row']['form_identifier'] ?? '', true);
        $this->getInvalidItems($selectedValues, $params['items']);
    }

    public function getFormElements(array &$params): void
    {
        $formIdentifier = $params['row']['form_identifier'][0] ?? null;
        if ($formIdentifier) {
            $formPersistenceManager = $this->getFormPersistenceManager();
            $elements = FormUtility::getFormElements($formPersistenceManager, $formIdentifier);
            $params['items'] = array_map(function (array $element) {
                return [$element['label'] ?? $element['identifier'], $element['identifier']];
            }, $elements);
        }
        $selectedValues = GeneralUtility::trimExplode(',', $params['row']['header_elements'], true);
        $this->getInvalidItems($selectedValues, $params['items']);
    }

    private function getFormPersistenceManager(): FormPersistenceManagerInterface
    {
        $yamlSource = GeneralUtility::makeInstance(YamlSource::class);
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $filePersistenceSlot = GeneralUtility::makeInstance(FilePersistenceSlot::class);
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        return GeneralUtility::makeInstance(
            FormPersistenceManager::class,
            $yamlSource,
            $storageRepository,
            $filePersistenceSlot,
            $resourceFactory,
            $configurationManager,
            $cacheManager,
        );
    }

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
}
