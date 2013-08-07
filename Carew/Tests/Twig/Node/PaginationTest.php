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
        $node = new Pagination($node, 20);
        $tests[] = array($node, <<<'EOF'
public function getNbItems(array $context)
{
    $context = $this->env->mergeGlobals($context);

    return count(twig_reverse_filter($this->env, twig_sort_filter(range(0, 100))));
}

public function getMaxPerPage()
{
    return 20;
}
EOF
        );

        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false));
        $moduleNode = $env->parse($env->tokenize('{% do collection|reverse %}'));
        $node = $moduleNode->getNode('body')->getNode(0)->getNode('expr');
        $node = new Pagination($node, 20);
        $tests[] = array($node, <<<'EOF'
public function getNbItems(array $context)
{
    $context = $this->env->mergeGlobals($context);

    return count(twig_reverse_filter($this->env, (isset($context["collection"]) ? $context["collection"] : null)));
}

public function getMaxPerPage()
{
    return 20;
}
EOF
        );

        return $tests;
    }
}
