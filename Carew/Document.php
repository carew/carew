<?php

namespace Carew;

use Symfony\Component\Finder\SplFileInfo;

class Document
{
    const TYPE_POST    = 'post';
    const TYPE_PAGE    = 'page';
    const TYPE_API     = 'api';
    const TYPE_UNKNOWN = 'unknown';

    private $body;
    private $file;
    private $filePath;
    private $layout;
    private $metadatas;
    private $path;
    private $title;
    private $toc;
    private $type;
    private $vars;

    public function __construct(SplFileInfo $file = null, $filePath = null, $type = self::TYPE_UNKNOWN)
    {
        $this->file      = $file;
        $this->filePath  = $filePath;
        $this->type      = $type;

        $this->layout    = false;
        $this->metadatas = array('tags' => array(), 'navigation' => array());
        $this->toc       = array();
        $this->vars      = array();

        if ($file && is_file($file)) {
            $this->path = $file->getBaseName();
            $this->title = $file->getBaseName();
            $this->body = file_get_contents($file);
        }
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

    public function addMetadatas(array $metadatas)
    {
        $this->metadatas = array_replace_recursive($this->metadatas, $metadatas);

        return $this;
    }

    public function getMetadata($key, $default = null)
    {
        return array_key_exists($key, $this->metadatas) ? $this->metadatas[$key] : $default;
    }

    public function setMetadata($key, $value)
    {
        $this->metadatas[$key] = $value;

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
        $path = ltrim($this->path, '/');
        if (0 === $nb = substr_count($path, DIRECTORY_SEPARATOR)) {
            return '.';
        }

        return rtrim(str_repeat('../', $nb), '/');
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

    public function getType()
    {
        return $this->type;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function getVar($key, $default = null)
    {
        return array_key_exists($key, $this->vars) ? $this->vars[$key] : $default;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;

        return $this;
    }

    public function setVar($key, $value)
    {
        $this->vars[$key] = $value;

        return $this;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }
}
