<?php

declare(strict_types=1);

namespace Remind\FormLog\Domain\Repository;

use DateInterval;
use DateTime;
use DateTimeZone;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class LogEntryRepository extends Repository
{
    public function findByFormIdentifier(string $formIdentifier): QueryResultInterface
    {
        $query = $this->createQuery();
        $query
            ->matching($query->equals('form_identifier', $formIdentifier))
            ->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }

    public function findByMaxAge(int $days, array $storagePages): QueryResultInterface
    {
        $dateInterval = DateInterval::createFromDateString($days . ' days');
        $timeZone = new DateTimeZone('UTC');
        $maxDate = new DateTime('now', $timeZone);
        $maxDate->sub($dateInterval);
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds($storagePages);
        $timestamp = $maxDate->getTimestamp();
        $query->matching($query->lessThan('crdate', $timestamp));
        return $query->execute();
    }

    public function persistAll(): void
    {
        $this->persistenceManager->persistAll();
    }
}
