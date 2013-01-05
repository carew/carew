<?php

namespace Carew;

use Symfony\Component\Finder\SplFileInfo;

class Document
{
    private $body;
    private $file;
    private $layout;
    private $metadatas;
    private $path;
    private $rootPath;
    private $title;
    private $toc;
    private $vars;

    public function __construct(SplFileInfo $file = null)
    {
        $this->body      = '';
        $this->file      = $file;
        $this->layout    = 'default';
        $this->metadatas = array('tags' => array(), 'navigation' => array());
        $this->path      = $file ? $file->getBaseName() : '.';
        $this->rootPath  = '.';
        $this->title     = $file ? $file->getBaseName() : '.';
        $this->toc       = array();
        $this->vars      = array();
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    public function getMetadatas()
    {
        return $this->metadatas;
    }

    public function setMetadatas($metadatas, $merge = true)
    {
        if ($merge) {
            $this->metadatas = array_replace_recursive($this->metadatas, $metadatas);
        } else {
            $this->metadatas = $metadatas;

        }

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getRootPath()
    {
        if (0 === $nb = substr_count($this->path, DIRECTORY_SEPARATOR)) {
            return '.';
        }

        return rtrim(str_repeat('../', $nb + 1), '/');
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getToc()
    {
        return $this->toc;
    }

    public function setToc($toc)
    {
        $this->toc = $toc;

        return $this;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;

        return $this;
    }
}
