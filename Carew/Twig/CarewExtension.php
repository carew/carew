<?php

namespace Carew\Twig;

use Carew\Document;

use Carew\Twig\NodeVisitor\Paginator;

class CarewExtension extends \Twig_Extension
{
    public function getNodeVisitors()
    {
        return array(
            new Paginator(),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render_document_toc',  array($this, 'renderDocumentToc'),       array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_document_*',    array($this, 'renderDocumentAttribute'), array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_document',      array($this, 'renderDocument'),          array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_documents',     array($this, 'renderDocuments'),         array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_pagination',    array($this, 'renderPagination'),        array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('paginate',             function() { } ),
            new \Twig_SimpleFunction('path',                 array($this, 'path'),                    array('needs_environment' => true)),
            new \Twig_SimpleFunction('link',                 array($this, 'link'),                    array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }

    public function renderDocumentToc(\Twig_Environment $twig, $toc = null, $deep = 0)
    {
        if (null === $toc) {
            $toc = $this->getCarewGlobals($twig)->document;
        }

        if (is_object($toc) && $toc instanceof Document) {
            $toc = $toc->getToc();
        }

        if (!is_array($toc)) {
            throw new InvalidArgumentException('First argument given to render_document_toc must be a Document or an array of TOC');
        }

        if (1 == count($toc)) {
            $first = reset($toc);
            if (!isset($first['title'])) {
                $toc = $first['children'];
            }
        }

        $parameters = array('children' => $toc, 'deep' => $deep);

        return $this->renderBlock($twig, 'document_toc', $parameters);
    }

    public function renderDocumentAttribute(\Twig_Environment $twig, $attribute, Document $document = null)
    {
        if (null === $document) {
            $document = $this->getCarewGlobals($twig)->document;
        }

        $parameters = array('document' => $document);

        return $this->renderBlock($twig, 'document_'.$attribute, $parameters);
    }

    public function renderDocument(\Twig_Environment $twig, Document $document)
    {
        $parameters = array('document' => $document);

        return $this->renderBlock($twig, $document->getType(), $parameters);
    }

    public function renderDocuments(\Twig_Environment $twig, array $documents = array(), array $pages = array(), $currentPage = null)
    {
        $parameters = array('documents' => $documents);

        $documentsBlock = $this->renderBlock($twig, 'documents', $parameters);

        if (0 < count($pages) && null !== $currentPage) {
            $documentsBlock .= $this->renderPagination($twig, $pages, $currentPage);
        }

        return $documentsBlock;
    }

    public function renderPagination(\Twig_Environment $twig, array $pages, $currentPage)
    {
        $parameters = array(
            'pages' => $pages,
            'current_page' => $currentPage,
        );

        return $this->renderBlock($twig, 'pagination', $parameters);
    }

    public function renderBlock(\Twig_Environment $twig, $block, array $parameters = array())
    {
        $template = $twig->loadTemplate('blocks.html.twig');

        $parameters = $twig->mergeGlobals($parameters);

        $level = ob_get_level();
        ob_start();
        try {
            $rendered = $template->renderBlock($block, $parameters);
            ob_end_clean();

            return $rendered;
        } catch (\Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
    }

    public function path(\Twig_Environment $twig, $filePath)
    {
        $globals = $twig->getGlobals();
        $documents = $globals['carew']->documents;

        if (!array_key_exists($filePath, $documents)) {
            throw new \InvalidArgumentException(sprintf('Unable to find path: "%s" in all documents', $filePath));
        }

        return $this->renderDocumentAttribute($twig, 'path', $documents[$filePath]);
    }

    public function link(\Twig_Environment $twig, $filePath, $title = null, array $attrs = array())
    {
        $globals = $twig->getGlobals();
        $documents = $globals['carew']->documents;

        if (!array_key_exists($filePath, $documents)) {
            throw new \InvalidArgumentException(sprintf('Unable to find path: "%s" in all documents', $filePath));
        }

        $parameters = array(
            'document' => $documents[$filePath],
            'title' => $title ?: $documents[$filePath]->getTitle(),
            'attrs' => $attrs,
        );

        return $this->renderBlock($twig, 'document_link', $parameters);
    }

    public function getName()
    {
        return 'carew';
    }

    private function getCarewGlobals(\Twig_Environment $twig)
    {
        $globals = $twig->getGlobals();

        return $globals['carew'];
    }
}
