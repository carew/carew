<?php

namespace Carew\Twig;

abstract class Template extends \Twig_Template
{
    public function getNbsItems(array $context)
    {
        return array();
    }

    public function getMaxesPerPage()
    {
        return array();
    }
}
