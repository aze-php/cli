<?php
namespace AZE\console\command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandConfiguration extends \Symfony\Component\Console\Command\Command
{
    protected $parameters = array();

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->parameters as $key=>$value) {
            $this->parameters[$key] = $input->getOption($key) ?: true;
        }

        if (file_exists($this->parameters['config']) && is_readable($this->parameters['config'])) {
            $config = parse_ini_file($this->parameters['config'], true);

            if ($config) {
                if (isset($config['aze'])) {
                    $config = $config['aze'];
                }

                foreach ($this->parameters as $key=>$value) {
                    if (isset($config[$key])) {
                        $this->parameters[$key] = $config[$key];
                    }
                }
            }
        }

        $output->writeln('');
        $output->writeln('Configuration used');
        $output->writeln('==================');
        foreach ($this->parameters as $key=>$value) {
            $output->writeln("\t* " . $key . ' : ' . $this->parameters[$key]);
        }
        $output->writeln('');
    }
}