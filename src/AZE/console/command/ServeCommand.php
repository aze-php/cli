<?php

namespace AZE\console\command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends \Symfony\Component\Console\Command\Command
{
    private $publicDir = array('htmldocs', 'public', 'web', 'www');

    private $parameters = array('host'=>null, 'port'=>null, 'publicDir'=>null, 'config'=>null);

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Serve an AZE application.')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'host use to serve your application', 'localhost')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'port use to serve your application', 80)
            ->addOption('publicDir', null, InputOption::VALUE_REQUIRED, 'directory containing your public files and your index.php', 'web')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Configuration file to serve your application', 'config.properties')
            ->addOption('open', 'o', InputOption::VALUE_NONE, 'Open your default browser once the server is launched');
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
        foreach ($this->parameters as $key=>$value) {
            $this->parameters[$key] = $input->getOption($key);
        }

        $this->computePropertyFile($input);

        $this->searchPublicDir();

        $output->writeln('Configuration used');
        $output->writeln('==================');
        foreach ($this->parameters as $key=>$value) {
            $output->writeln("\t* " . $key . ' : ' . $this->parameters[$key]);
        }


        $output->writeln('');
        $output->writeln('WARNING : use this only in a dev environment !');
        $output->writeln('');
        $output->writeln('Launch !');
        $output->writeln('Server listen on : ' . $this->parameters['host'] . ':' . $this->parameters['port']);

        if ($input->getOption('open')) {
            $command = 'open http://' . $this->parameters['host'] . ':' . $this->parameters['port'];
            switch (true) {
                case stristr(PHP_OS, 'WIN'):
                    $command = 'start /max http://' . $this->parameters['host'] . ':' . $this->parameters['port'];
                    break;
                case stristr(PHP_OS, 'LINUX'):
                    $command = 'xdg-open http://' . $this->parameters['host'] . ':' . $this->parameters['port'];
                    break;
                case stristr(PHP_OS, 'DAR'):
                default :
                    break;
            }

            shell_exec($command);
        }
        shell_exec('php -S ' . $this->parameters['host'] . ':' . $this->parameters['port'] . ' -t ' . $this->parameters['publicDir']);
    }

    private function computePropertyFile(InputInterface $input)
    {
        $this->parameters['config'] = $input->getOption('config');
        if (file_exists($this->parameters['config']) && is_readable($this->parameters['config'])) {
            $config = parse_ini_file($this->parameters['config'], true);

            if ($config) {
                if (isset($config['server'])) {
                    $config = $config['server'];
                }

                foreach ($this->parameters as $key=>$value) {
                    if (isset($config[$key])) {
                        $this->parameters[$key] = $config[$key];
                    }
                }
            }
        }
    }

    private function searchPublicDir()
    {
        if (!file_exists($this->parameters['publicDir'])) {
            foreach ($this->publicDir as $dir) {
                if (file_exists($dir)) {
                    $this->parameters['publicDir'] = $dir;
                    break;
                }
            }
        }
    }
}