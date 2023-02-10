<?php

declare(strict_types=1);

namespace Remind\FormLog\Domain\Finishers;

use Remind\FormLog\Domain\Model\LogEntry;
use Remind\FormLog\Domain\Repository\LogEntryRepository;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\FinisherInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class LogFinisher extends AbstractFinisher
{
    private const EXCLUDED_TYPES = [
        'Fieldset',
        'GridRow',
        'Hidden',
        'Honeypot',
        'StaticText',
    ];
    protected $defaultOptions = [
        'storagePid' => 0,
    ];

    public function __construct(
        private LogEntryRepository $logEntryRepository
    ) {
    }

    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $formDefinition = $formRuntime->getFormDefinition();

        $formValues = $this->finisherContext->getFormValues();
        $formData = [];

        foreach ($formValues as $identifier => $elementValue) {
            // Get current form element
            $element = $formDefinition->getElementByIdentifier($identifier);

            // Process element if type is not excluded
            if ($element instanceof GenericFormElement && !in_array($element->getType(), self::EXCLUDED_TYPES)) {
                // Build form data
                $formData[$element->getIdentifier()] = $elementValue ?? null;
            }

            // Process attachments
            if (
                $element instanceof FileUpload &&
                $elementValue instanceof FileReference
            ) {
                // Process file path from FileReference
                $filePath = $elementValue->getOriginalResource()->getOriginalFile()->getPublicUrl();
                $formData['attachments'][] = $filePath;
            }
        }

        $finisherData = array_reduce($formDefinition->getFinishers(), function (array $result, AbstractFinisher $finisher) use ($formRuntime) {
            $finisherIdentifier = $finisher->getFinisherIdentifier();
            if ($finisherIdentifier !== 'Log') {
                $result[$finisherIdentifier] = array_map(function (mixed $value) use ($formRuntime) {
                    return $this->substituteRuntimeReferences($value, $formRuntime);
                }, $finisher->options);
            }
            return $result;
        }, []);

        // Get storage page from finisher option
        $storagePid = (int) $this->parseOption('storagePid');

        $formIdentifier = $formDefinition->getRenderingOptions()['_originalIdentifier'];

        $logEntry = new LogEntry();

        $logEntry->setPid($storagePid);
        $logEntry->setFormIdentifier($formIdentifier);
        $logEntry->setFormData(json_encode($formData));
        $logEntry->setFinisherData(json_encode($finisherData));

        $this->logEntryRepository->add($logEntry);
    }
}
