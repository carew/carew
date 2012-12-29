<?php

namespace Carew\Console;

use Carew\Console\Command;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Carew', $version = '0.1-dev');

        $this->add(new Command\GeneratePost());
    }
}
