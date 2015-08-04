<?php

namespace Carew\Twig\NodeVisitor;

use Carew\Twig\Node\Pagination as PaginationNode;

class Paginator implements \Twig_NodeVisitorInterface
{
    private $currentModule;
    private $currentRenderDocuments;
    private $currentNumberOfPagination;
    private $maxPerPage;

    public function __construct($maxPerPage = 10)
    {
        $this->maxPerPage = $maxPerPage;
    }

    public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Module) {
            $this->currentModule = $node;
            $this->currentNumberOfPagination = 0;
        } elseif ($node instanceof \Twig_Node_Expression_Function) {
            $name = $node->getAttribute('name');
            if ('paginate' == $name) {
                $node = $this->enterPaginationFilterNode($node, $env);

                ++$this->currentNumberOfPagination;

                return $node;
            }
            if ('render_documents' == $name) {
                $this->currentRenderDocuments = $node;
            }
        }

        return $node;
    }

    public function enterPaginationFilterNode(\Twig_Node_Expression_Function $node, \Twig_Environment $env)
    {
        $args = $node->getNode('arguments');

        if (!$args->hasNode(0)) {
            throw new \Twig_Error_Syntax('Missing first argument of "paginate" function.');
        }

        // extract $maxPerPage;
        if ($args->hasNode(1)) {
            $arg = $args->getNode(1);
            if (!$arg instanceof \Twig_Node_Expression_Constant) {
                throw new \Twig_Error_Syntax('Second argument (optional) of "paginate" function should be an integer.');
            }
            $maxPerPage = (integer) $arg->getAttribute('value');
        } else {
            $maxPerPage = $this->maxPerPage;
        }

        $this->alterRenderDocumentsWithPagination();

        $nodeToPaginate = $args->getNode(0);

        // Set-up the PaginationNode
        $extra = $this->currentModule->getNode('blocks');
        if (!$extra->hasNode('pagination')) {
            $extra->setNode('pagination', new PaginationNode());
        }
        $extra->getNode('pagination')->addNodeToPaginate($nodeToPaginate, $maxPerPage);

        // Filter the node with "|slice(offset, maxPerPage)"
        $slicedNode = new \Twig_Node_Expression_Filter(
            $nodeToPaginate,
            new \Twig_Node_Expression_Constant('slice', 1),
            new \Twig_Node(array(
                new \Twig_Node_Expression_Name(sprintf('__offset_%s__', $this->currentNumberOfPagination), 1),
                new \Twig_Node_Expression_Constant($maxPerPage, 1),
            )),
            1
        );

        return $slicedNode;
    }

    public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Module) {
            $this->currentModule = null;
        } elseif ($node instanceof \Twig_Node_Expression_Function) {
            $name = $node->getAttribute('name');
            if ('render_documents' == $name) {
                $this->currentRenderDocuments = null;
            }
        }

        return $node;
    }

    public function getPriority()
    {
        0;
    }

    private function alterRenderDocumentsWithPagination()
    {
        if (!$this->currentRenderDocuments) {
            return;
        }

        $args = $this->currentRenderDocuments->getNode('arguments');
        $args->setNode(1, new \Twig_Node_Expression_Name(sprintf('__pages_%s__', $this->currentNumberOfPagination), 1));
        $args->setNode(2, new \Twig_Node_Expression_Name(sprintf('__current_page_%s__', $this->currentNumberOfPagination), 1));
    }
}
