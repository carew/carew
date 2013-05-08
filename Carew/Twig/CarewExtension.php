<?php

namespace Carew\Twig;

use Carew\Document;

class CarewExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render_document_url',  array($this, 'renderDocumentUrl'),       array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_document_path', array($this, 'renderDocumentUrl'),       array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_document_*',    array($this, 'renderDocumentAttribute'), array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_document',      array($this, 'renderDocument'),          array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_documents',     array($this, 'renderDocuments'),         array('is_safe' => array('html'), 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_*',             array($this, 'renderBlock'),             array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }

    public function renderDocumentUrl(\Twig_Environment $twig, Document $document)
    {
        $parameters = array('document' => $document);

        return $this->renderDocumentAttribute($twig, 'url', $document, $parameters);
    }

    public function renderDocumentAttribute(\Twig_Environment $twig, $attribute, Document $document)
    {
        $parameters = array('document' => $document);

        return $this->renderBlock($twig, 'document_'.$attribute, $parameters);
    }

    public function renderDocument(\Twig_Environment $twig, Document $document)
    {
        $parameters = array('document' => $document);

        return $this->renderBlock($twig, $document->getType(), $parameters);
    }

    public function renderDocuments(\Twig_Environment $twig, array $documents = array())
    {
        $parameters = array('documents' => $documents);

        return $this->renderBlock($twig, 'documents', $parameters);
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

    public function getName()
    {
        return 'carew';
    }
}
