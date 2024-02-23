<?php

declare(strict_types=1);

namespace Remind\FormLog\Command;

use Remind\FormLog\Domain\Model\LogEntry;
use Remind\FormLog\Domain\Repository\LogEntryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class DeleteLogCommand extends Command
{
    private const ARGUMENT_DAYS = 'days';
    private const ARGUMENT_STORAGE_PAGES = 'storage-pages';
    private const OPTION_SOFT_DELETE = 'soft-delete';
    private DataMapper $dataMapper;
    private string $tableName;

    public function __construct(
        private readonly LogEntryRepository $logEntryRepository,
        private readonly DataHandler $dataHandler,
        DataMapper $dataMapper,
    ) {
        parent::__construct();
        $this->tableName = $dataMapper->getDataMap(LogEntry::class)->getTableName();
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this
            ->setDescription('Delete log entries saved in database after specififed amount of days.')
            ->addArgument(
                self::ARGUMENT_DAYS,
                InputArgument::REQUIRED,
                'Maximum number of days to keep log entries',
            )
            ->addArgument(
                self::ARGUMENT_STORAGE_PAGES,
                InputArgument::REQUIRED,
                'Storage page(s) containing log entries to be deleted, separated by comma',
            )
            ->addOption(
                self::OPTION_SOFT_DELETE,
                null,
                InputOption::VALUE_NONE,
                'Set deleted flag to 1 instead of hard delete',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $days = (int)$input->getArgument(self::ARGUMENT_DAYS);
        $storagePages = GeneralUtility::intExplode(',', $input->getArgument(self::ARGUMENT_STORAGE_PAGES), true);
        $softDelete = $input->getOption(self::OPTION_SOFT_DELETE);

        if ($days < 1) {
            $io->error('Value for days must be >= 1');
            return Command::FAILURE;
        }

        $entries = $this->logEntryRepository->findByMaxAge($days, $storagePages);
        $count = $entries->count();

        if (!$io->isQuiet()) {
            $io->note('Found ' . $count . ' log entries to be deleted.');
            if ($softDelete) {
                $io->note('Set deleted flag instead of hard delete.');
            }
        }

        if ($count > 0) {
            if (!$softDelete) {
                $this->dataHandler->start([], []);
            }

            if ($io->isVerbose()) {
                $io->section('Log entries to be deleted:');
            }

            /** @var LogEntry $entry */
            foreach ($entries as $entry) {
                $this->log($entry, $io);

                if ($softDelete) {
                    $this->logEntryRepository->remove($entry);
                } else {
                    $this->dataHandler->deleteRecord($this->tableName, $entry->getUid(), true, true);
                }
            }

            if ($softDelete) {
                $this->logEntryRepository->persistAll();
            }
        }

        $io->success('All done!');

        return Command::SUCCESS;
    }

    protected function log(LogEntry $entry, SymfonyStyle $io): void
    {
        if ($io->isVerbose()) {
            $io->write('{ uid: ' . $entry->getUid());
        }
        if ($io->isVeryVerbose()) {
            $io->write(', date: ' . $entry->getCrdate());
            $io->write(', form: ' . $entry->getFormIdentifier());
        }
        if ($io->isVerbose()) {
            $io->writeLn(' }');
        }
    }
}
