<?php

namespace AZE\console\command;

use AZE\ComposerHelper;
use AZE\Resource;
use AZE\Utils;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends CommandConfiguration
{
    private $composer;

    private $className;
    private $namespace;

    protected $parameters = array('sourceDir'=>null, 'config'=>null);

    /**
     * CommandConfiguration the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('controller')
            ->setDescription('Create a new AZE controller')
            ->addArgument('name', InputArgument::REQUIRED, "Name of your controller")
            ->addOption('sourceDir', null, InputOption::VALUE_REQUIRED, 'Directory containing your sources', 'src/controller')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Configuration file to serve your application', 'config.properties');
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

        $this->className = ucfirst(Utils::toCamelCase(basename($input->getArgument('name'))));

        $this->namespace = 'controller';
        if (dirname($input->getArgument('name')) !== '.') {
            $this->namespace = 'controller\\' . Utils::toCamelCase(dirname($input->getArgument('name')));
        }

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
            $data['autoload']['psr-4']['controller\\'] = $this->parameters['sourceDir'];
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
        $classContent = Resource::get('Controller.php');

        $classContent = str_replace('%namespace%', $this->namespace, $classContent);
        $classContent = str_replace('%classname%', $this->className, $classContent);

        $directory = dirname(getcwd() . DIRECTORY_SEPARATOR . $this->parameters['sourceDir'] . DIRECTORY_SEPARATOR . $input->getArgument('name'));

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $directory . DIRECTORY_SEPARATOR . $this->className . '.php';
        file_put_contents($file, $classContent);
    }
}