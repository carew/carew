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
    public function testNodeVisitorThrowExceptionIfCollectionIsNotDefined()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ paginate() }}'));
    }

    /**
     * @expectedException Twig_Error_Syntax
     * @expectedExceptionMessage Second argument of "paginate" function should be an integer.
     */
    public function testNodeVisitorThrowExceptionIfMaxPerPageValueIsNotAConstant()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ paginate(collection, maxPerPage) }}'));
    }

    public function testNodeVisitorAddSliceFilter()
    {
        $env = $this->createEnv();

        $stream = $env->parse($env->tokenize('{{ paginate(collection, 10) }}'));

        $nodeFilter = $stream->getNode('body')->getNode(0)->getNode('expr');
        $this->assertInstanceOf('Twig_Node_Expression_Filter', $nodeFilter);
        $this->assertInstanceOf('Twig_Node_Expression_Constant', $nodeFilter->getNode('filter'));
        $this->assertSame('slice', $nodeFilter->getNode('filter')->getAttribute('value'));

        $arguments = $nodeFilter->getNode('arguments');
        $this->assertInstanceOf('Twig_Node_Expression_Name', $arguments->getNode(0));
        $this->assertSame('__offset__', $arguments->getNode(0)->getAttribute('name'));
        $this->assertInstanceOf('Twig_Node_Expression_Constant', $arguments->getNode(1));
        $this->assertSame(10, $arguments->getNode(1)->getAttribute('value'));

        $extraNode = $stream->getNode('extra');
        $this->assertInstanceOf('Twig_Node_Extra', $extraNode);
        $this->assertInstanceOf('Carew\Twig\Node\Pagination', $extraNode->getNode(0));
        $this->assertInstanceOf('Twig_Node_Expression_Name', $extraNode->getNode(0)->getNode('node'));
        $this->assertSame('collection', $extraNode->getNode(0)->getNode('node')->getAttribute('name'));
        $this->assertSame(10, $extraNode->getNode(0)->getAttribute('maxPerPage'));
    }

    private function createEnv()
    {
        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false));
        $env->addNodeVisitor(new Paginator());
        $env->addFunction(new \Twig_SimpleFunction('paginate', function() { }));
        $env->addGlobal('collection', range(1, 100));

        return $env;
    }
}
