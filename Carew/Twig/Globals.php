<?php

namespace Carew\Twig;

class Globals
{
    public $site = array();
    public $config = array();

    public $documents = array();
    public $posts = array();
    public $pages = array();
    public $apis = array();

    public $tags = array();
    public $navigations = array();

    public $relativeRoot;
    public $currentPath;
    public $document;

    public $extra = array();

    public function __construct(array $config = array())
    {
        $this->config = $config;
        if (array_key_exists('site', $config)) {
            $this->site = $config['site'];
        }
    }

    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                $this->extra[$key] = $value;
            }
        }

        return $this;
    }
}
