<?php

namespace Carew\Tests\Twig\Node;

use Carew\Twig\Node\Pagination;

class PaginationTest extends \Twig_Test_NodeTestCase
{
    public function getTests()
    {
        $tests = array();

        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false));
        $moduleNode = $env->parse($env->tokenize('{% do range(0, 100)|sort|reverse %}'));
        $node = $moduleNode->getNode('body')->getNode(0)->getNode('expr');
        $paginationNode = new Pagination();
        $paginationNode->addNodeToPaginate($node, 20);
        $tests[] = array($paginationNode, <<<'EOF'
public function getNbsItems(array $context)
{
    $context = $this->env->mergeGlobals($context);

    return array(
        count(twig_reverse_filter($this->env, twig_sort_filter(range(0, 100)))),
    );
}

public function getMaxesPerPage()
{
    return array(
        20,
    );
}
EOF
        );

        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false));
        $moduleNode = $env->parse($env->tokenize('{% do collection|reverse %}'));
        $node = $moduleNode->getNode('body')->getNode(0)->getNode('expr');
        $paginationNode = new Pagination();
        $paginationNode->addNodeToPaginate($node, 20);
        $tests[] = array($paginationNode, <<<'EOF'
public function getNbsItems(array $context)
{
    $context = $this->env->mergeGlobals($context);

    return array(
        count(twig_reverse_filter($this->env,         // line 1
(isset($context["collection"]) ? $context["collection"] : null))),
    );
}

public function getMaxesPerPage()
{
    return array(
        20,
    );
}
EOF
        );

        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false));
        $moduleNode = $env->parse($env->tokenize('{% do collection|reverse %}'));
        $node = $moduleNode->getNode('body')->getNode(0)->getNode('expr');
        $paginationNode = new Pagination();
        $paginationNode->addNodeToPaginate($node, 20);
        $paginationNode->addNodeToPaginate($node, 10);
        $tests[] = array($paginationNode, <<<'EOF'
public function getNbsItems(array $context)
{
    $context = $this->env->mergeGlobals($context);

    return array(
        count(twig_reverse_filter($this->env,         // line 1
(isset($context["collection"]) ? $context["collection"] : null))),
        count(twig_reverse_filter($this->env, (isset($context["collection"]) ? $context["collection"] : null))),
    );
}

public function getMaxesPerPage()
{
    return array(
        20,
        10,
    );
}
EOF
        );

        return $tests;
    }
}
