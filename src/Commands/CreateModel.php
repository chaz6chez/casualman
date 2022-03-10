<?php
declare(strict_types=1);

namespace CasualMan\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModel extends Command
{
    protected static $defaultName = 'create:model';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('database', InputArgument::REQUIRED, 'database name')
            ->addArgument('table',InputArgument::REQUIRED, 'table name')
            ->addArgument('namespace',InputArgument::OPTIONAL, 'table name','CasualMan\Application\Model')
            ->setDescription('Create model class.')
            ->setHelp("This command allows you to quickly create a model.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $database = strtolower($input->getArgument('database'));
        $table = strtolower($input->getArgument('table'));
        $namespace = $input->getArgument('namespace');
        $output->writeln("Create table {$table} for database {$database}");
        $model = lower2camel(line2hump($table) . 'Model');
        $file = ROOT_PATH . "/src/Application/Model/{$model}.php";
        $this->createModel($database, $table, $namespace, $file);
        return Command::SUCCESS;
    }

    /**
     * @param string $database
     * @param string $table
     * @param string $namespace
     * @param string $file
     */
    protected function createModel(string $database, string $table, string $namespace, string $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $class = lower2camel(line2hump($table) . 'Model');

        $content = <<<EOF
<?php
declare(strict_types=1);

namespace {$namespace};

class {$class} extends BaseModel 
{
    protected \$_dbName = '{$database}';
    
    protected \$_table  = '{$table}';
   
    
}

EOF;
        file_put_contents($file, $content);
    }
}
