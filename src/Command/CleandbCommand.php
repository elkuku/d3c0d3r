<?php

namespace App\Command;

use App\Repository\WaypointRepository;
use App\Service\WayPointHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleandbCommand extends Command
{
    protected static $defaultName = 'cleandb';

    private EntityManagerInterface $entityManager;
    private WaypointRepository $waypointRepository;
    private WayPointHelper $wayPointHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        WaypointRepository $waypointRepository,
        WayPointHelper $wayPointHelper
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->waypointRepository = $waypointRepository;
        $this->wayPointHelper = $wayPointHelper;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument(
                'arg1',
                InputArgument::OPTIONAL,
                'Argument description'
            )
            ->addOption(
                'option1',
                null,
                InputOption::VALUE_NONE,
                'Option description'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $errorCount = 0;
        $warningCount = 0;
        $io = new SymfonyStyle($input, $output);

        $waypoints = $this->waypointRepository->findAll();

        $progressBar = new ProgressBar($output, count($waypoints));

        foreach ($waypoints as $waypoint) {
            if (!$waypoint->getLat() || !$waypoint->getLon()) {
                $io->error(
                    sprintf('"%s" missing location', $waypoint->getName())
                );
                $errorCount++;
                $this->entityManager->remove($waypoint);
            }

            if (!$waypoint->getName()) {
                $io->error(sprintf('"%s" missing name', $waypoint->getName()));
                $errorCount++;
                $this->entityManager->remove($waypoint);
            }

            if ('undefined' === $waypoint->getName()) {
                $io->error(sprintf('"%s" name', $waypoint->getName()));
                $errorCount++;
                $this->entityManager->remove($waypoint);
            }

            $cleanName = $this->wayPointHelper->cleanName($waypoint->getName());

            if ($waypoint->getName() !== $cleanName) {
                $io->warning(
                    sprintf(
                        '"%s" dirty title "%s" clean title',
                        $waypoint->getName(),
                        $cleanName
                    )
                );
                $warningCount++;
                $waypoint->setName($cleanName);
                $this->entityManager->persist($waypoint);
            }

            $progressBar->advance();
        }

        $this->entityManager->flush();

        $progressBar->finish();

        if ($errorCount) {
            $io->error(sprintf('There have been %d errors', $errorCount));
        }
        if ($warningCount) {
            $io->warning(sprintf('There have been %d warnings', $warningCount));
        }

        if (!$errorCount && !$warningCount) {
            $io->success('Database is clean.');

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
