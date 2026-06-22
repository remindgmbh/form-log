<?php

declare(strict_types=1);

namespace Remind\FormLog\Tests\Unit\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Remind\FormLog\Command\DeleteLogCommand;
use Remind\FormLog\Domain\Model\LogEntry;
use Remind\FormLog\Domain\Repository\LogEntryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(DeleteLogCommand::class)]
class DeleteLogCommandTest extends UnitTestCase
{
    #[Test]
    public function executeReturnsFailureForInvalidDays(): void
    {
        $repository = $this->createMock(LogEntryRepository::class);
        $repository
            ->expects(self::never())
            ->method('findByMaxAge');

        $dataHandler = $this->createMock(DataHandler::class);
        $dataHandler
            ->expects(self::never())
            ->method('start');

        $commandTester = new CommandTester($this->createCommand($repository, $dataHandler));

        $exitCode = $commandTester->execute([
            'days' => '0',
            'storage-pages' => '12',
        ]);

        self::assertSame(Command::FAILURE, $exitCode);
    }

    #[Test]
    public function executeUsesSoftDeleteFlow(): void
    {
        $entry = new LogEntry();
        $entry->_setProperty('uid', 42);

        $entries = $this->createQueryResultMock([$entry]);

        $repository = $this->createMock(LogEntryRepository::class);
        $repository
            ->expects(self::once())
            ->method('findByMaxAge')
            ->with(30, [12, 34])
            ->willReturn($entries);
        $repository
            ->expects(self::once())
            ->method('remove')
            ->with($entry);
        $repository
            ->expects(self::once())
            ->method('persistAll');

        $dataHandler = $this->createMock(DataHandler::class);
        $dataHandler
            ->expects(self::never())
            ->method('start');
        $dataHandler
            ->expects(self::never())
            ->method('deleteRecord');

        $commandTester = new CommandTester($this->createCommand($repository, $dataHandler));

        $exitCode = $commandTester->execute([
            '--soft-delete' => true,
            'days' => '30',
            'storage-pages' => '12,34',
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
    }

    #[Test]
    public function executeUsesHardDeleteFlow(): void
    {
        $entry = new LogEntry();
        $entry->_setProperty('uid', 99);

        $entries = $this->createQueryResultMock([$entry]);

        $repository = $this->createMock(LogEntryRepository::class);
        $repository
            ->expects(self::once())
            ->method('findByMaxAge')
            ->with(10, [5])
            ->willReturn($entries);
        $repository
            ->expects(self::never())
            ->method('remove');
        $repository
            ->expects(self::never())
            ->method('persistAll');

        $dataHandler = $this->createMock(DataHandler::class);
        $dataHandler
            ->expects(self::once())
            ->method('start')
            ->with([], []);
        $dataHandler
            ->expects(self::once())
            ->method('deleteRecord')
            ->with('tx_formlog_domain_model_logentry', 99, true, true);

        $commandTester = new CommandTester($this->createCommand($repository, $dataHandler));

        $exitCode = $commandTester->execute([
            'days' => '10',
            'storage-pages' => '5',
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
    }

    private function createCommand(LogEntryRepository $repository, DataHandler $dataHandler): DeleteLogCommand
    {
        $dataMap = $this->createMock(DataMap::class);
        $dataMap
            ->method('getTableName')
            ->willReturn('tx_formlog_domain_model_logentry');

        $dataMapper = $this->createMock(DataMapper::class);
        $dataMapper
            ->method('getDataMap')
            ->with(LogEntry::class)
            ->willReturn($dataMap);

        return new DeleteLogCommand($repository, $dataHandler, $dataMapper);
    }

    /**
     * @param LogEntry[] $entries
     * @return QueryResultInterface<LogEntry>
     */
    private function createQueryResultMock(array $entries): QueryResultInterface
    {
        /** @var QueryResultInterface<LogEntry>&\PHPUnit\Framework\MockObject\MockObject $queryResult */
        $queryResult = $this->createMock(QueryResultInterface::class);
        $index = 0;

        $queryResult
            ->method('count')
            ->willReturn(count($entries));
        $queryResult
            ->method('rewind')
            ->willReturnCallback(static function () use (&$index): void {
                $index = 0;
            });
        $queryResult
            ->method('valid')
            ->willReturnCallback(static function () use (&$index, $entries): bool {
                return isset($entries[$index]);
            });
        $queryResult
            ->method('current')
            ->willReturnCallback(static function () use (&$index, $entries): ?LogEntry {
                return $entries[$index] ?? null;
            });
        $queryResult
            ->method('key')
            ->willReturnCallback(static function () use (&$index): int {
                return $index;
            });
        $queryResult
            ->method('next')
            ->willReturnCallback(static function () use (&$index): void {
                $index++;
            });

        return $queryResult;
    }
}
