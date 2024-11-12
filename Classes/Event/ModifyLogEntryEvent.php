<?php

declare(strict_types=1);

namespace Remind\FormLog\Event;

use Remind\FormLog\Domain\Model\LogEntry;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;

class ModifyLogEntryEvent
{
    /**
     * @var mixed[]
     */
    private array $additionalData = [];

    private LogEntry $logEntry;

    private FinisherContext $finisherContext;

    public function __construct(LogEntry $logEntry, FinisherContext $finisherContext)
    {
        $this->logEntry = $logEntry;
        $this->finisherContext = $finisherContext;
    }

    /**
     * @return mixed[]
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /**
     * @param mixed[] $additionalData
     */
    public function setAdditionalData(array $additionalData): self
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    public function getLogEntry(): LogEntry
    {
        return $this->logEntry;
    }

    public function getFinisherContext(): FinisherContext
    {
        return $this->finisherContext;
    }
}
