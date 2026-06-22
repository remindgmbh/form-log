<?php

declare(strict_types=1);

namespace Remind\FormLog\Tests\Unit\Domain\Finishers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Remind\FormLog\Domain\Finishers\LogFinisher;
use Remind\FormLog\Domain\Model\LogEntry;
use Remind\FormLog\Domain\Repository\LogEntryRepository;
use Remind\FormLog\Event\ModifyLogEntryEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(LogFinisher::class)]
class LogFinisherTest extends UnitTestCase
{
    #[Test]
    public function executeAddsLogEntryWithFilteredDataAndAdditionalData(): void
    {
        $visibleElement = $this->createMock(GenericFormElement::class);
        $visibleElement
            ->method('getType')
            ->willReturn('Text');
        $visibleElement
            ->method('getIdentifier')
            ->willReturn('name');

        $hiddenElement = $this->createMock(GenericFormElement::class);
        $hiddenElement
            ->method('getType')
            ->willReturn('Hidden');
        $hiddenElement
            ->method('getIdentifier')
            ->willReturn('hiddenField');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition
            ->method('getElementByIdentifier')
            ->willReturnCallback(
                static function (string $identifier) use ($visibleElement, $hiddenElement): GenericFormElement {
                    return $identifier === 'name' ? $visibleElement : $hiddenElement;
                }
            );
        $formDefinition
            ->method('getRenderingOptions')
            ->willReturn([
                '_originalIdentifier' => 'contact',
            ]);

        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime
            ->method('getFormDefinition')
            ->willReturn($formDefinition);

        $finisherContext = $this->createMock(FinisherContext::class);
        $finisherContext
            ->method('getFormRuntime')
            ->willReturn($formRuntime);
        $finisherContext
            ->method('getFormValues')
            ->willReturn([
                'hiddenField' => 'secret',
                'name' => 'Alice',
            ]);

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->willReturnCallback(static function (ModifyLogEntryEvent $event): ModifyLogEntryEvent {
                $event->setAdditionalData(['source' => 'unit-test']);
                return $event;
            });

        $repository = $this->createMock(LogEntryRepository::class);
        $repository
            ->expects(self::once())
            ->method('add')
            ->with(self::callback(static function (LogEntry $entry): bool {
                return $entry->getFormIdentifier() === 'contact'
                    && $entry->getFormData() === '{"name":"Alice"}'
                    && $entry->getAdditionalData() === '{"source":"unit-test"}';
            }));

        $finisher = new LogFinisher($repository, $eventDispatcher);
        $finisher->setOptions(['storagePid' => 123]);

        self::assertNull($finisher->execute($finisherContext));
    }

    #[Test]
    public function executeThrowsExceptionForNegativeStoragePid(): void
    {
        $formDefinition = $this->createMock(FormDefinition::class);

        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime
            ->method('getFormDefinition')
            ->willReturn($formDefinition);

        $finisherContext = $this->createMock(FinisherContext::class);
        $finisherContext
            ->method('getFormRuntime')
            ->willReturn($formRuntime);
        $finisherContext
            ->method('getFormValues')
            ->willReturn([]);

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects(self::never())
            ->method('dispatch');

        $repository = $this->createMock(LogEntryRepository::class);
        $repository
            ->expects(self::never())
            ->method('add');

        $finisher = new LogFinisher($repository, $eventDispatcher);
        $finisher->setOptions(['storagePid' => -1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid storagePid');
        $finisher->execute($finisherContext);
    }
}
