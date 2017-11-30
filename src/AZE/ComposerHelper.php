<?php
/**
 * Created by IntelliJ IDEA.
 * User: brice_leboulch
 * Date: 29/11/2017
 * Time: 17:15
 */

namespace AZE;


class ComposerHelper
{
    private $nullFile = '/dev/null';
    private $testCommand = 'which';

    public function __construct()
    {
        if (stristr(PHP_OS, 'WIN')) {
            $this->nullFile = 'nul';
            $this->testCommand = 'where';
        }

        return $this;
    }

    private function getComposerCommand()
    {
        $composer = null;

        if (`$this->testCommand composer 2> $this->nullFile`) {
            $composer = 'composer';
        } elseif (`$this->testCommand composer.phar 2> $this->nullFile`) {
            $composer = 'composer.phar';
        } elseif (file_exists(getcwd().'/composer.phar')) {
            $composer = getcwd().'/composer.phar';
        }

        if (is_null($composer)) {
            throw new RuntimeException('Can\'t find composer binary');
        }

        return $composer;
    }

    public function execute($command)
    {
        $composer = $this->getComposerCommand();
        return `$composer $command`;
    }
}