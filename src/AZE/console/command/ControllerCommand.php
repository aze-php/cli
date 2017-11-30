<?php

namespace AZE\console\command;

use AZE\ComposerHelper;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends \Symfony\Component\Console\Command\Command
{
    private $composer;

    private $className;
    private $namespace;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('controller')
            ->setDescription('Create a new AZE controller')
            ->addArgument('name', InputArgument::REQUIRED, "Name of your controller")
            ->addOption('dir', null, InputOption::VALUE_OPTIONAL, 'directory with your controllers', 'src/controller');
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

        $cleanName = str_ireplace('/', '\\', $input->getArgument('name'));
        $this->className = strtoupper(basename($cleanName)[0]) . substr(basename($cleanName), 1, strlen($cleanName) - 2);
        $this->namespace = 'controller\\' . dirname($input->getArgument('name'));

        $this->addPsr4Namespace($input);
        $this->createController($input);
    }

    /**
     * find composer.json
     * @return string
     */
    private function getComposerJson()
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'composer.json';
    }

    /**
     * Add psr-4 parameters to composer.json
     * @param InputInterface $input
     */
    private function addPsr4Namespace(InputInterface $input)
    {
        $data = array();
        $update = false;

        // Get composer.json content
        $file = $this->getComposerJson();

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        }

        // Create psr-4 autoloading
        if (!isset($data['autoload'])) {
            $data['autoload'] = array();
            $update = true;
        }

        if (!isset($data['autoload']['psr-4'])) {
            $data['autoload']['psr-4'] = array();
            $update = true;
        }

        if (!isset($data['autoload']['psr-4']['controller\\'])) {
            $data['autoload']['psr-4']['controller\\'] = $input->getOption('dir');
            $update = true;
        }

        if ($update) {
            // Add content to composer.json
            file_put_contents($file, json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

            $this->composer->execute("update");
        }
    }

    private function createController(InputInterface $input)
    {
        $classContent = file_get_contents(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'resources/Controller.php');

        $classContent = str_replace('%namespace%', $this->namespace, $classContent);
        $classContent = str_replace('%classname%', $this->className, $classContent);

        $directory = dirname(getcwd() . DIRECTORY_SEPARATOR . $input->getOption('dir') . DIRECTORY_SEPARATOR . $input->getArgument('name'));

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $directory . DIRECTORY_SEPARATOR . $this->className . '.php';
        file_put_contents($file, $classContent);
    }
}