<?php
declare(strict_types=1);

namespace CasualMan\Commands;

use Kernel\Routers\AbstractRouter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RouteList extends Command
{
    protected static $defaultName = 'route:list';

    protected function configure() : void
    {
        $this
            ->addOption('details', 'd',InputOption::VALUE_NONE, 'show details')
            ->setDescription('Show route list.')
            ->setHelp("This command can display all registered routes.");
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $headers = ($detail = $input->getOption('details')) ?
            ['name (uri)', 'method', 'callback', 'middleware'] :
            ['name (uri)', 'method', 'callback'];
        $rows = [];
        foreach (AbstractRouter::getRoutes() as $route) {
            foreach ($route->getMethods() as $method) {
                $cb = $route->getCallback();
                $cb = $cb instanceof \Closure ? 'Closure' : (is_array($cb) ? json_encode($cb) : var_export($cb, 1));
                $rows[] = $detail ?
                    [$route->getName(), $method, $cb, json_encode($route->getMiddlewaresString() ?: [])] :
                    [$route->getName(), $method, $cb];
            }
        }
        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
        return Command::SUCCESS;
    }
}
