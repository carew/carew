<?php

namespace Carew;

use Symfony\Component\Filesystem\Filesystem;
use Twig_Environment;

class Renderer
{
    private $filesystem;
    private $target;
    private $twig;

    public function __construct(Twig_Environment $twig, $target, Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();
        $this->target     = $target;
        $this->twig       = $twig;

        $this->filesystem->mkdir($target);
    }

    public function buildDocument(Document $document)
    {
        if (false === $document->getLayout()) {
            $rendered = $document->getBody();
        } else {
            $layout = $document->getLayout();
            $layout .= false === strpos($layout, '.twig') ? '.html.twig' : '';
            $rendered = $this->twig->render($layout, array_replace($document->getVars(), array(
                'document'     => $document,
                'relativeRoot' => $document->getRootPath(),
                'currentPath'  => $document->getPath(),
            )));
        }

        $target = $this->target.'/'.$document->getPath();
        $this->filesystem->mkdir(dirname($target));
        file_put_contents($target, $rendered);
    }
}
