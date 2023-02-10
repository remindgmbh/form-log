<?php

declare(strict_types=1);

namespace Remind\FormLog\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Configuration extends AbstractEntity
{
    protected string $formIdentifier = '';
    protected string $headerElements = '';
    protected string $finisherOptions = '';
    protected int $itemsPerPage = 25;

    public function getFormIdentifier(): string
    {
        return $this->formIdentifier;
    }

    public function setFormIdentifier(string $formIdentifier): self
    {
        $this->formIdentifier = $formIdentifier;

        return $this;
    }

    public function getHeaderElements(): string
    {
        return $this->headerElements;
    }

    public function setHeaderElements(string $headerElements): self
    {
        $this->headerElements = $headerElements;

        return $this;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function getFinisherOptions(): string
    {
        return $this->finisherOptions;
    }

    public function setFinisherOptions(string $finisherOptions): self
    {
        $this->finisherOptions = $finisherOptions;

        return $this;
    }
}
