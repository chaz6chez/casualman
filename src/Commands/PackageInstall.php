<?php
declare(strict_types=1);

namespace CasualMan\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageInstall extends Command
{
    protected static $defaultName = 'package:install';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Install Package Config.')
            ->setHelp("This command allows you to install a package config.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // todo
        return Command::SUCCESS;
    }
}
