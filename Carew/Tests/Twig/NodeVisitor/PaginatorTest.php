<?php

namespace Carew\Tests\Twig\NodeVisitor;

use Carew\Twig\NodeVisitor\Paginator;

class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function getNodeVisitorAlterNothingIfNotNeededTests()
    {
        return array(
            array('{% extends "foo" %}{% block content %}{{ parent() }}{% endblock %}'),
            array('{{ collection|slice(1, 10) }}'),
            array('{{ collection[2:10] }}'),
        );
    }

    /**
     * @dataProvider getNodeVisitorAlterNothingIfNotNeededTests
     */
    public function testNodeVisitorAlterNothingIfNotNeeded($template)
    {
        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false));
        $stream = $env->parse($env->tokenize($template));

        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false));
        $env->addNodeVisitor(new Paginator());
        $streamWithNodeVisitorRegistred = $env->parse($env->tokenize($template));

        $this->assertSame((string) $stream, (string) $streamWithNodeVisitorRegistred);
    }

    /**
     * @expectedException Twig_Error_Syntax
     * @expectedExceptionMessage Missing first argument of "paginate" function.
     */
    public function testNodeVisitorThrowExceptionIfFirstArgumentIsNotDefined()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ paginate() }}'));
    }

    /**
     * @expectedException Twig_Error_Syntax
     * @expectedExceptionMessage Second argument (optional) of "paginate" function should be an integer.
     */
    public function testNodeVisitorThrowExceptionIfMaxPerPageValueIsNotAConstant()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ paginate(collection, maxPerPage) }}'));
    }

    public function testNodeVisitorAddSliceFilterAndPaginationNode()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ paginate(collection, 6) }}'));
        // Transformed in "{{ collection|slice(__offset_0__, 6) }}"

        // collection|slice(__offset_0__, 6)
        $nodeFilter = $stream->getNode('body')->getNode(0)->getNode('expr');
        $this->assertInstanceOf('Twig_Node_Expression_Filter', $nodeFilter);
        $this->assertNodeFilterHasSlice($nodeFilter, 6);

        $blocksNode = $stream->getNode('blocks');
        $this->assertInstanceOf('Twig_Node', $blocksNode);

        $paginationNode = $blocksNode->getNode('pagination');
        $this->assertInstanceOf('Carew\Twig\Node\Pagination', $paginationNode);

        $this->assertInstanceOf('Twig_Node_Expression_Name', $paginationNode->getNode(0));
        $this->assertSame('collection', $paginationNode->getNode(0)->getAttribute('name'));
        $this->assertSame(array(6), $paginationNode->getAttribute('maxesPerPage'));
    }

    public function testNodeVisitorAlterRenderDocumentAndAddSliceFilterAndPaginationNode()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ render_documents(paginate(collection)) }}'));
        // Transformed in "{{ render_documents(collection|slice(__offset_0__, 10), __pages_0__, __current_page_0__) }}"

        // render_documents(...)
        $nodeFunction = $stream->getNode('body')->getNode(0)->getNode('expr');
        $this->assertInstanceOf('Twig_Node_Expression_Function', $nodeFunction);
        $this->assertSame('render_documents', $nodeFunction->getAttribute('name'));

        $arguments = $nodeFunction->getNode('arguments');
        $this->assertInstanceOf('Twig_Node_Expression_Name', $arguments->getNode(1));
        $this->assertSame('__pages_0__', $arguments->getNode(1)->getAttribute('name'));
        $this->assertInstanceOf('Twig_Node_Expression_Name', $arguments->getNode(2));
        $this->assertSame('__current_page_0__', $arguments->getNode(2)->getAttribute('name'));

        // collection|slice(__offset_0__, 10)
        $nodeFilter = $arguments->getNode(0);
        $this->assertInstanceOf('Twig_Node_Expression_Filter', $nodeFilter);
        $this->assertNodeFilterHasSlice($nodeFilter, 10);

        // Tests on extra
        $blocksNode = $stream->getNode('blocks');
        $this->assertInstanceOf('Twig_Node', $blocksNode);

        $paginationNode = $blocksNode->getNode('pagination');
        $this->assertInstanceOf('Carew\Twig\Node\Pagination', $paginationNode);

        $this->assertInstanceOf('Twig_Node_Expression_Name', $paginationNode->getNode(0));
        $this->assertSame('collection', $paginationNode->getNode(0)->getAttribute('name'));
        $this->assertFalse($paginationNode->hasNode(1));
        $this->assertSame(array(10), $paginationNode->getAttribute('maxesPerPage'));
    }

    public function testNodeVisitorAlterRenderDocumentAndAddSliceFilterAndPaginationNodeWithManyPaginate()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ render_documents(paginate(collection)) }} {{ render_documents(paginate(collection2, 6)) }}'));
        // Transformed in :
        // {{ render_documents(collection|slice(__offset_0__, 10), __pages_0__, __current_page_0__) }}
        // {{ render_documents(collection2|slice(__offset_1__, 6),  __pages_1__, __current_page_1__) }}

        foreach (array(
            0 => array('nodeItem' => 0, 'maxPerPage' => 10),
            1 => array('nodeItem' => 2, 'maxPerPage' => 6),
        ) as $currentNumberOfPagination => $data) {
            $nodeFunction = $stream->getNode('body')->getNode(0)->getNode($data['nodeItem'])->getNode('expr');

            // render_documents(...)
            $this->assertInstanceOf('Twig_Node_Expression_Function', $nodeFunction);
            $this->assertSame('render_documents', $nodeFunction->getAttribute('name'));

            $arguments = $nodeFunction->getNode('arguments');
            $this->assertInstanceOf('Twig_Node_Expression_Name', $arguments->getNode(1));
            $this->assertSame("__pages_${currentNumberOfPagination}__", $arguments->getNode(1)->getAttribute('name'));
            $this->assertInstanceOf('Twig_Node_Expression_Name', $arguments->getNode(2));
            $this->assertSame("__current_page_${currentNumberOfPagination}__", $arguments->getNode(2)->getAttribute('name'));

            // collection|slice(__offset_0__, 10)
            $nodeFilter = $arguments->getNode(0);
            $this->assertInstanceOf('Twig_Node_Expression_Filter', $nodeFilter);
            $this->assertNodeFilterHasSlice($nodeFilter, $data['maxPerPage'], $currentNumberOfPagination);
        }

        // Test on extra
        $blocksNode = $stream->getNode('blocks');
        $this->assertInstanceOf('Twig_Node', $blocksNode);

        $paginationNode = $blocksNode->getNode('pagination');
        $this->assertInstanceOf('Carew\Twig\Node\Pagination', $paginationNode);

        $this->assertInstanceOf('Twig_Node_Expression_Name', $paginationNode->getNode(0));
        $this->assertSame('collection', $paginationNode->getNode(0)->getAttribute('name'));
        $this->assertInstanceOf('Twig_Node_Expression_Name', $paginationNode->getNode(1));
        $this->assertSame('collection2', $paginationNode->getNode(1)->getAttribute('name'));
        $this->assertFalse($paginationNode->hasNode(2));
        $this->assertSame(array(10, 6), $paginationNode->getAttribute('maxesPerPage'));
    }

    private function assertNodeFilterHasSlice(\Twig_Node_Expression_Filter $nodeFilter, $maxPerPage, $currentNumberOfPagination = 0)
    {
        $this->assertInstanceOf('Twig_Node_Expression_Constant', $nodeFilter->getNode('filter'));
        $this->assertSame('slice', $nodeFilter->getNode('filter')->getAttribute('value'));

        $arguments = $nodeFilter->getNode('arguments');
        $this->assertInstanceOf('Twig_Node_Expression_Name', $arguments->getNode(0));
        $this->assertSame("__offset_${currentNumberOfPagination}__", $arguments->getNode(0)->getAttribute('name'));
        $this->assertInstanceOf('Twig_Node_Expression_Constant', $arguments->getNode(1));
        $this->assertSame($maxPerPage, $arguments->getNode(1)->getAttribute('value'));
    }

    private function createEnv()
    {
        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $env->addNodeVisitor(new Paginator());
        $env->addFunction(new \Twig_SimpleFunction('paginate', function () { }));
        $env->addFunction(new \Twig_SimpleFunction('render_documents', function () { }));
        $env->addGlobal('collection', range(1, 100));

        return $env;
    }
}
