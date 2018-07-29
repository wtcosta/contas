<?php
namespace HXPHP\System\Configs\Environments;

use HXPHP\System\Configs;

class EnvironmentProduction extends Configs\AbstractEnvironment
{
    public function __construct()
    {
        parent::__construct();
        $this->servers = [
            '144.217.248.45'
        ];
    }
}