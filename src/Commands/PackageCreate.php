<?php
//declare(strict_types=1);

namespace CasualMan\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PackageCreate extends Command
{
    const INTERNAL_PATH = ROOT_PATH . '/src/Package';
    const COMPOSER_PATH = ROOT_PATH . '/pkg/';

    protected static $defaultName = 'package:create';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('composer','c',InputOption::VALUE_NONE, 'composer package')
            ->addArgument('name', InputArgument::REQUIRED, 'package name')
            ->setDescription('Create package.')
            ->setHelp("This command allows you to quickly create a model.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = strtolower($input->getArgument('name'));
        $composer = $input->getOption('composer');
        list($userName, $packageName) = $name = explode('/', $name);
        if(count($name) !== 2){
            $block = new FormatterHelper();
            $block->formatBlock(['Error','Package name format error. for example foo/test'],'error');
            $output->writeln($block);
            return Command::FAILURE;
        }
        $userName = strtolower($userName);
        $packageName = $composer ? hump2line($packageName) : ucfirst(line2hump($packageName));
        
        return Command::SUCCESS;
    }


    protected function createConfig()
    {

    }

    protected function createInstaller()
    {

    }

    protected function createComposerJson()
    {

    }
}
