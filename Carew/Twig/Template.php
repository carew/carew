<?php

namespace Carew\Twig;

abstract class Template extends \Twig_Template
{
    public function getNbItems(array $context)
    {
        return null;
    }

    public function getMaxPerPage()
    {
        return null;
    }
}
