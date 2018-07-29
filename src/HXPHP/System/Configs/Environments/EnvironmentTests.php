<?php
namespace HXPHP\System\Configs\Environments;

use HXPHP\System\Configs;

class EnvironmentTests extends Configs\AbstractEnvironment
{
    public function __construct()
    {
        ini_set('display_errors', 1);
        $this->servers = [
            'localhost',
            '127.0.0.1',
            '::1',
            '10.0.0.114'
        ];
    }
}