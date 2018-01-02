<?php

namespace AZE\console\command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends \Symfony\Component\Console\Command\Command
{
    private $dir;

    private $nullFile = "nul";

    private $projectPath = null;
    private $projectName = null;

    /**
     * CommandConfiguration the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cron')
            ->setDescription('Create a cron')
            ->addArgument('script', InputArgument::OPTIONAL, "Script to execute");


        switch (true) {
            case stristr(PHP_OS, 'WIN'):
                $this->nullFile = 'nul';
                break;
            case stristr(PHP_OS, 'LINUX'):
            case stristr(PHP_OS, 'DAR'):
            default :
                $this->nullFile = '/dev/null';
                break;
        }
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $composer = $this->findComposer();
        if (is_null($composer)) {
            throw new RuntimeException('Can\'t find composer binary');
        }

        $this->projectPath = getcwd();
        if (!is_null($input->getArgument("name"))) {
            $this->projectPath .= DIRECTORY_SEPARATOR . $input->getArgument("name");
            if (!file_exists($this->projectPath)) {
                $output->writeln("Create directory : $this->projectPath");
                mkdir($this->projectPath);
            }
            chdir($this->projectPath);
        }

        $this->projectName = basename($this->projectPath);

        $this->createRequiredDirectories($output);

        shell_exec("$composer require aze/aze");
        shell_exec("$composer require aze/dumper");

        $output->writeln("Installation done");
        $output->writeln("You can use the following command to serve your application : aze serve");
    }

    private function createRequiredDirectories(OutputInterface $output)
    {
        if (!file_exists('private')) {
            $output->writeln('Create "private" directory');
            mkdir('private');
        }

        if (!file_exists('web')) {
            $output->writeln('Create "web" directory');
            mkdir('web');
        }

        if (!file_exists('composer.json')) {
            touch('composer.json');
            $content = <<<EOF
{
    "name": "$this->projectName",
    "description": "$this->projectName"
}
EOF;

            file_put_contents('composer.json', $content);
        }

        if (!file_exists('web/index.php')) {
            touch('web/index.php');
            $content = <<<EOF
<?php
require_once(__DIR__ . '/../vendor/autoload.php');
AZE\core\Initializer::initialize();
EOF;
            file_put_contents('web/index.php', $content);
        }
    }

    /**
     * Find composer binary
     *
     * @return null|string
     */
    private function findComposer()
    {
        $composer = null;

        if (`which composer 2> ' . $this->nullFile . '`) {
            $composer = 'composer';
        } elseif (`which composer.phar 2> ' . $this->nullFile . '`) {
            $composer = 'composer.phar';
        } elseif (file_exists(getcwd().'/composer.phar')) {
            $composer = getcwd().'/composer.phar';
        }

        return $composer;
    }

    /**
     * find composer.json
     * @return string
     */
    private function findComposerJson()
    {
        return $this->projectPath . DIRECTORY_SEPARATOR . '/composer.json';
    }

    private function addPsr4Namespace()
    {
        $file = $this->findComposerJson();
        $data = json_decode(file_get_contents($file), true);
        $data["autoload"]["psr-4"][] = array($key => $namespace);
        file_put_contents($output, json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    }
}