<?php

declare(strict_types=1);

namespace Remind\FormLog\Domain\Repository;

use Remind\FormLog\Domain\Model\Configuration;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ConfigurationRepository extends Repository
{
    public function findByFormIdentifier(string $formIdentifier): ?Configuration
    {
        $query = $this->createQuery();
        $query
            ->matching($query->equals('form_identifier', $formIdentifier))
            ->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute()->getFirst();
    }
}
