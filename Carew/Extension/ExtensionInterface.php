<?php

namespace Carew\Extension;

interface ExtensionInterface
{
    public function register(\Pimple $container);
}
