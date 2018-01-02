<?php

namespace AZE\console\command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends CommandConfiguration
{
    private $publicDir = array('htmldocs', 'public', 'web', 'www');

    protected $parameters = array('host'=>null, 'port'=>null, 'publicDir'=>null, 'config'=>null, 'open'=>null);

    /**
     * CommandConfiguration the command options.
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
            ->addOption('open', 'o', InputOption::VALUE_OPTIONAL, 'Open your default browser once the server is launched', 'false');
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
        $this->searchPublicDir();

        $output->writeln('');
        $output->writeln('WARNING : use this only in a dev environment !');
        $output->writeln('');
        $output->writeln('Launch !');
        $output->writeln('Server will listen on : ' . $this->parameters['host'] . ':' . $this->parameters['port']);

        if ($this->parameters['open'] !== 'false') {
            $this->openBrowser();
        }

        $this->serve();
    }

    private function openBrowser()
    {
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

    private function serve()
    {
        return shell_exec('php -S ' . $this->parameters['host'] . ':' . $this->parameters['port'] . ' -t ' . $this->parameters['publicDir']);
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