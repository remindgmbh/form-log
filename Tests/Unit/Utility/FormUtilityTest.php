<?php

declare(strict_types=1);

namespace Remind\FormLog\Tests\Unit\Utility;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Remind\FormLog\Utility\FormUtility;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(FormUtility::class)]
class FormUtilityTest extends UnitTestCase
{
    #[Test]
    public function getFormElementsReturnsFlattenedRenderableElements(): void
    {
        $formPersistenceManager = $this->createMock(FormPersistenceManagerInterface::class);
        $formPersistenceManager
            ->method('listForms')
            ->willReturn([
                ['identifier' => 'contact', 'persistenceIdentifier' => 'contact-form'],
            ]);
        $formPersistenceManager
            ->method('load')
            ->with('contact-form', [], [])
            ->willReturn([
                'renderables' => [
                    [
                        'renderables' => [
                            [
                                'identifier' => 'name',
                                'label' => 'Name',
                                'properties' => ['required' => true],
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'email',
                                'label' => 'E-Mail',
                                'type' => 'Email',
                            ],
                        ],
                    ],
                ],
            ]);

        $result = FormUtility::getFormElements($formPersistenceManager, [], 'contact');

        self::assertSame(['name', 'email'], array_keys($result));
        self::assertSame(
            [
                'identifier' => 'name',
                'label' => 'Name',
                'properties' => [
                    'required' => true,
                ],
                'type' => 'Text',
            ],
            $result['name']
        );
        self::assertSame(
            [
                'identifier' => 'email',
                'label' => 'E-Mail',
                'properties' => null,
                'type' => 'Email',
            ],
            $result['email']
        );
    }

    #[Test]
    public function getFormElementsReturnsEmptyArrayForUnknownFormIdentifier(): void
    {
        $formPersistenceManager = $this->createMock(FormPersistenceManagerInterface::class);
        $formPersistenceManager
            ->method('listForms')
            ->willReturn([
                ['identifier' => 'contact', 'persistenceIdentifier' => 'contact-form'],
            ]);
        $formPersistenceManager
            ->expects(self::never())
            ->method('load');

        $result = FormUtility::getFormElements($formPersistenceManager, [], 'newsletter');

        self::assertSame([], $result);
    }
}
