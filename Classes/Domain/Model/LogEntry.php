<?php

declare(strict_types=1);

namespace Remind\FormLog\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class LogEntry extends AbstractEntity
{
    protected string $formIdentifier = '';
    protected string $formData = '';
    protected string $finisherData = '';
    protected ?int $crdate = null;

    public function getFormIdentifier(): string
    {
        return $this->formIdentifier;
    }

    public function setFormIdentifier(string $formIdentifier): self
    {
        $this->formIdentifier = $formIdentifier;

        return $this;
    }

    public function getFormData(): string
    {
        return $this->formData;
    }

    public function setFormData(string $formData): self
    {
        $this->formData = $formData;

        return $this;
    }

    public function getFinisherData(): string
    {
        return $this->finisherData;
    }

    public function setFinisherData(string $finisherData): self
    {
        $this->finisherData = $finisherData;

        return $this;
    }

    public function getCrdate(): ?int
    {
        return $this->crdate;
    }

    public function setCrdate(?int $crdate): self
    {
        $this->crdate = $crdate;

        return $this;
    }
}
