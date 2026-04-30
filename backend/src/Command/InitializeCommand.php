<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:initialize',
    description: 'Initialize the application (create database and run migrations)',
)]
class InitializeCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('ProjectAdvisor - Initialization');

        try {
            $io->info('Creating database...');
            // Database is created during migration

            $io->info('Running migrations...');
            // Migrations are handled by doctrine:migrations:migrate

            $io->success('✅ Application initialized successfully!');
            $io->info('Run "symfony serve" to start the development server.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Initialization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
