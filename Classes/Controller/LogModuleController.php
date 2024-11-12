<?php

declare(strict_types=1);

namespace Remind\FormLog\Controller;

use Psr\Http\Message\ResponseInterface;
use Remind\FormLog\Domain\Model\LogEntry;
use Remind\FormLog\Domain\Repository\ConfigurationRepository;
use Remind\FormLog\Domain\Repository\LogEntryRepository;
use Remind\FormLog\Utility\FormUtility;
use RuntimeException;
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
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $formIdentifiers = $this->getAvailableFormIdentifiers();

        $currentFormIdentifier = $formIdentifier ?? $formIdentifiers[0] ?? null;

        $formIdentifiers = array_map(function (string $formIdentifier) use ($currentFormIdentifier) {
            return [
                'active' => $currentFormIdentifier === $formIdentifier,
                'link' => $this->uriBuilder->uriFor(null, ['formIdentifier' => $formIdentifier]),
                'name' => $formIdentifier,
            ];
        }, $formIdentifiers);

        $totalAmount = 0;

        if ($currentFormIdentifier) {
            $queryResult = $this->logEntryRepository->findByFormIdentifier($currentFormIdentifier);
            $configuration = $this->configurationRepository->findByFormIdentifier($currentFormIdentifier);
            $headerElementsIdentifiers = GeneralUtility::trimExplode(',', $configuration?->getHeaderElements() ?? '');
            $paginator = new QueryResultPaginator($queryResult, $currentPage, $configuration?->getItemsPerPage() ?? 25);
            $pagination = new SimplePagination($paginator);
            $totalAmount = $queryResult->count();
            $moduleTemplate->assign('pagination', $pagination);
            $moduleTemplate->assign('paginator', $paginator);
            $elements = FormUtility::getFormElements($this->formPersistenceManager, $currentFormIdentifier);
            $moduleTemplate->assign('elements', $elements);
            $headerElements = array_filter($elements, function (array $element) use ($headerElementsIdentifiers) {
                return in_array($element['identifier'], $headerElementsIdentifiers);
            });
            $moduleTemplate->assign('headerElements', $headerElements);
            $paginatedItems = $paginator->getPaginatedItems();

            $entries = [];
            foreach ($paginatedItems as $item) {
                $entries[] = $this->formatLogEntry($item, $elements);
            }

            $moduleTemplate->assign('entries', $entries);
        }

        $moduleTemplate->assign('currentFormIdentifier', $currentFormIdentifier);
        $moduleTemplate->assign('formIdentifiers', $formIdentifiers);
        $moduleTemplate->assign('totalAmount', $totalAmount);

        $moduleTemplate->setTitle(
            $this->getLanguageService()->sL(
                'LLL:EXT:rmnd_form_log/Resources/Private/Language/locallang_module.xlf:mlang_tabs_tab'
            ),
            $currentFormIdentifier ?? '',
        );

        return $moduleTemplate->renderResponse();
    }

    public function downloadCsvAction(string $formIdentifier): ResponseInterface
    {
        $queryResult = $this->logEntryRepository->findByFormIdentifier($formIdentifier);

        $resource = fopen('php://temp', 'w+');

        if (!$resource) {
            throw new RuntimeException('Could not open temporary file handle');
        }

        foreach ($queryResult as $entry) {
            $json = json_decode($entry->getFormData(), true);
            fputcsv($resource, $json);
        }

        $storagePid = $this->request->getQueryParams()['id'];
        $filename = join('_', [$formIdentifier, $storagePid, date('YmdHis')]);

        return $this->responseFactory
            ->createResponse()
            ->withBody($this->streamFactory->createStreamFromResource($resource))
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment;filename="' . $filename . '.csv"');
    }

    /**
     * @param mixed[] $elements
     * @return mixed[]
     */
    private function formatLogEntry(LogEntry $logEntry, array $elements): array
    {
        $entry = [];
        $entry['formData'] = [];
        $entry['additionalData'] = [];
        $formData = json_decode($logEntry->getFormData(), true);
        $additionalData = json_decode($logEntry->getAdditionalData(), true) ?? [];

        foreach ($formData as $key => $value) {
            $entry['formData'][$key] =
                isset($elements[$key]) &&
                in_array($elements[$key]['type'], self::ELEMENTS_WITH_OPTIONS)
             ? $elements[$key]['properties']['options'][$value] ?? null : $value;
        }

        foreach ($additionalData as $key => $value) {
            $entry['additionalData'][$key] = is_string($value) ? $value : json_encode($value);
        }

        $entry['crdate'] = date('Y-m-d H:i:s', $logEntry->getCrdate());

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
        $queryResult = $queryBuilder->executeQuery();
        return $queryResult->fetchFirstColumn();
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
