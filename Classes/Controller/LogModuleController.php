<?php

declare(strict_types=1);

namespace Remind\FormLog\Controller;

use Psr\Http\Message\ResponseInterface;
use Remind\FormLog\Domain\Model\LogEntry;
use Remind\FormLog\Domain\Repository\ConfigurationRepository;
use Remind\FormLog\Domain\Repository\LogEntryRepository;
use Remind\FormLog\Utility\FormUtility;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

class LogModuleController extends ActionController
{
    private const ELEMENTS_WITH_OPTIONS = [
        'RadioButton',
        'SingleSelect',
        'MultiSelect',
        'MultiCheckbox',
    ];

    public function __construct(
        private readonly LogEntryRepository $logEntryRepository,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly FormPersistenceManagerInterface $formPersistenceManager,
        private readonly ConnectionPool $connectionPool,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {
    }

    public function listAction(?string $formIdentifier = null, int $currentPage = 1): ResponseInterface
    {
        $formIdentifiers = $this->getAvailableFormIdentifiers();

        $currentFormIdentifier = $formIdentifier ?? $formIdentifiers[0] ?? null;

        $formIdentifiers = array_map(function (string $formIdentifier) use ($currentFormIdentifier) {
            return [
                'name' => $formIdentifier,
                'link' => $this->uriBuilder->uriFor(null, ['formIdentifier' => $formIdentifier]),
                'active' => $currentFormIdentifier === $formIdentifier,
            ];
        }, $formIdentifiers);

        $totalAmount = 0;

        if ($currentFormIdentifier) {
            $queryResult = $this->logEntryRepository->findByFormIdentifier($currentFormIdentifier);
            $configuration = $this->configurationRepository->findByFormIdentifier($currentFormIdentifier);
            $visibleFinishers = GeneralUtility::trimExplode(',', $configuration?->getFinisherOptions() ?? '', true);
            $visibleFinishers = array_reduce($visibleFinishers, function (array $result, string $finisherOption) {
                [$key, $value] = GeneralUtility::trimExplode('_', $finisherOption, true);
                $result[$key][] = $value;
                return $result;
            }, []);
            $headerElementsIdentifiers = GeneralUtility::trimExplode(',', $configuration?->getHeaderElements() ?? '');
            $paginator = new QueryResultPaginator($queryResult, $currentPage, $configuration?->getItemsPerPage() ?? 25);
            $pagination = new SimplePagination($paginator);
            $totalAmount = $queryResult->count();
            $this->view->assign('pagination', $pagination);
            $this->view->assign('paginator', $paginator);
            $elements = FormUtility::getFormElements($this->formPersistenceManager, $currentFormIdentifier);
            $this->view->assign('elements', $elements);
            $headerElements = array_filter($elements, function (array $element) use ($headerElementsIdentifiers) {
                return in_array($element['identifier'], $headerElementsIdentifiers);
            });
            $this->view->assign('headerElements', $headerElements);
            $paginatedItems = $paginator->getPaginatedItems();

            $entries = [];
            foreach ($paginatedItems as $item) {
                $entries[] = $this->formatLogEntry($item, $visibleFinishers, $elements);
            }

            $this->view->assign('entries', $entries);
        }

        $this->view->assign('currentFormIdentifier', $currentFormIdentifier);
        $this->view->assign('formIdentifiers', $formIdentifiers);
        $this->view->assign('totalAmount', $totalAmount);

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setTitle(
            $this->getLanguageService()->sL(
                'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_module.xlf:mlang_tabs_tab'
            ),
            $currentFormIdentifier,
        );
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    private function formatLogEntry(LogEntry $logEntry, array $visibleFinishers, array $elements): array
    {
        $entry = [];
        $entry['data'] = [];
        $data = json_decode($logEntry->getFormData(), true);

        foreach ($data as $key => $value) {
            if (in_array($elements[$key]['type'], self::ELEMENTS_WITH_OPTIONS)) {
                $entry['data'][$key] = $elements[$key]['properties']['options'][$value];
            } else {
                $entry['data'][$key] = $value;
            }
        }

        $entry['finishers'] = [];
        $entry['crdate'] = date('Y-m-d H:i:s', $logEntry->getCrdate());
        $finishers = json_decode($logEntry->getFinisherData(), true);

        foreach ($visibleFinishers as $visibleFinisherIdentifier => $visibleFinisherOptions) {
            foreach ($visibleFinisherOptions as $visibleFinisherOption) {
                $value = $finishers[$visibleFinisherIdentifier][$visibleFinisherOption];
                $entry['finishers'][$visibleFinisherIdentifier][$visibleFinisherOption] = is_string($value)
                    ? $value
                    : json_encode($value);
            }
        }
        return $entry;
    }

    /**
     * @return string[]
     */
    private function getAvailableFormIdentifiers(): array
    {
        $config = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        $storagePid = $config['persistence']['storagePid'] ?? 0;

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_formlog_domain_model_logentry');
        $queryBuilder
            ->select('form_identifier')
            ->from('tx_formlog_domain_model_logentry')
            ->groupBy('form_identifier')
            ->where(
                $queryBuilder->expr()->eq('pid', $storagePid)
            );
        $queryResult = $queryBuilder->execute();
        return $queryResult->fetchFirstColumn();
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
