<?php
namespace AZE;

class Parameter
{
    private $config = array();

    public function __construct($configFilePath = null)
    {
        $this->config = parse_ini_file($configFilePath, true);
    }

    /**
     * @param $key
     */
    public function get($keys, $section)
    {
        if ($this->config) {
            $config = $this->config;

            if (isset($config[$section])) {
                $config = $config[$section];
            }

            foreach ($keys as $key => $value) {
                if (isset($config[$key])) {
                    $this->parameters[$key] = $config[$key];
                }
            }
        }
    }
}
