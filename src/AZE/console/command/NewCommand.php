<?php
namespace AZE\console\command;

use AZE\ComposerHelper;
use AZE\Resource;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends CommandConfiguration
{
    private $composer;

    private $projectPath = null;
    private $projectName = null;

    protected $parameters = array('sourceDir' => null, 'publicDir' => null, 'config' => null, 'without-aop' => null);

    /**
     * CommandConfiguration the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new AZE application')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                "Name of your application"
            )
            ->addOption(
                'sourceDir',
                'src',
                InputOption::VALUE_REQUIRED,
                'Directory containing your sources',
                'src'
            )
            ->addOption(
                'publicDir',
                'public',
                InputOption::VALUE_REQUIRED,
                'Directory containing your public files',
                'web'
            )
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Configuration file to serve your application',
                'config.properties'
            )
            ->addOption(
                'without-aop',
                null,
                InputOption::VALUE_OPTIONAL,
                'Deploy your application without aop activated',
                "true"
            );
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

        $this->create($output);

        $this->composer->execute("init -n --name={$this->projectName} --description={$this->projectName}");

        if (!$this->parameters['without-aop']) {
            $this->composer->execute("require goaop/framework");
        }

        $this->composer->execute("require aze/aze:dev-master");
        $this->composer->execute("require aze/dumper");

        $output->writeln("Installation done");
        $output->writeln("You can use the following command to serve your application : aze serve");
    }

    private function create(OutputInterface $output)
    {
        if (!file_exists($this->parameters['sourceDir'])) {
            $output->writeln("Create \"{$this->parameters['sourceDir']}\" directory");
            mkdir($this->parameters['sourceDir']);
        }

        if (!file_exists($this->parameters['publicDir'])) {
            $output->writeln("Create \"{$this->parameters['publicDir']}\" directory");
            mkdir($this->parameters['publicDir']);
        }

        $this->createIndex($this->parameters['publicDir'] . '/index.php', $output);
        $this->createConfigurationFile($this->parameters['sourceDir'] . '/config/config.json', $output);
        $this->createInitClass($this->parameters['sourceDir'] . '/Init.php', $output);
        $this->createRoutingFile($this->parameters['sourceDir'] . '/routing.xml', $output);
    }

    private function createIndex($file, OutputInterface $output)
    {
        $output->writeln("Create \"{$file}\"");

        if (file_exists($file)) {
            throw new \Exception('index file already exists');
        }

        $content = Resource::get('index.php');

        $aop = "";
        if (!$this->parameters['without-aop']) {
            $aop = Resource::get('aop.php');
            $aop = str_replace('%sourceDir%', $this->parameters['sourceDir'], $aop);
        }

        $content = str_replace('%aop%', $aop, $content);
        $content = str_replace('%sourceDir%', $this->parameters['sourceDir'], $content);

        file_put_contents($file, $content);
    }

    private function createConfigurationFile($file, OutputInterface $output)
    {
        $output->writeln("Create \"{$file}\"");

        if (file_exists($file)) {
            throw new \Exception('Configuration file already exists');
        } else {
            mkdir(dirname($file), 0755, true);
        }

        $content = Resource::get('config.json');

        $content = str_replace('%name%', $this->projectName, $content);

        file_put_contents($file, $content);
    }

    private function createInitClass($file, OutputInterface $output)
    {
        $output->writeln("Create \"{$file}\"");

        if (file_exists($file)) {
            throw new \Exception('Init class file already exists');
        }

        $content = Resource::get('Init.php');

        file_put_contents($file, $content);
    }


    private function createRoutingFile($file, OutputInterface $output)
    {
        $output->writeln("Create \"{$file}\"");

        if (file_exists($file)) {
            throw new \Exception('Routing file already exists');
        }

        $content = Resource::get('routing.xml');

        file_put_contents($file, $content);
    }
}
