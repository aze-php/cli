<?php

namespace AZE\console\command;

use AZE\ComposerHelper;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends \Symfony\Component\Console\Command\Command
{
    private $dir;

    private $composer;

    private $projectPath = null;
    private $projectName = null;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new AZE application')
            ->addArgument('name', InputArgument::OPTIONAL, "Name of your application");
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
        $this->composer = new ComposerHelper();

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

        $this->composer->execute("require aze/aze");
        $this->composer->execute("require aze/dumper");

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
}