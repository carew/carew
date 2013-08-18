<?php

namespace Carew\Twig\Node;

class Pagination extends \Twig_Node
{
    public function __construct()
    {
        parent::__construct(array(), array('maxesPerPage' => array()));
    }

    public function addNodeToPaginate(\Twig_Node $node, $maxPerPage)
    {
        $this->nodes[] = $node;

        $maxesPerPage = $this->getAttribute('maxesPerPage');
        $maxesPerPage[] = $maxPerPage;
        $this->setAttribute('maxesPerPage', $maxesPerPage);

        return $this;
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $this->compileGetNbItems($compiler);

        $this->compileMaxPerPage($compiler);
    }

    private function compileGetNbItems(\Twig_Compiler $compiler)
    {
        $compiler
            ->write("public function getNbsItems(array \$context)\n", "{\n")
            ->indent()
        ;

        $compiler->write("\$context = \$this->env->mergeGlobals(\$context);\n\n");

        $compiler->write("return array(\n");
        $compiler->indent();
        foreach ($nodes = $this->nodes as $node) {
            $compiler->write('count(');
            $compiler->subcompile($node);
            $compiler->raw("),\n");
        }
        $compiler->outdent();
        $compiler->write(");\n");

        $compiler
            ->outdent()
            ->write("}\n\n")
        ;
    }

    private function compileMaxPerPage(\Twig_Compiler $compiler)
    {
        $compiler
            ->write("public function getMaxesPerPage()\n", "{\n")
            ->indent()
        ;

        $compiler->write("return array(\n");
        $compiler->indent();
        foreach ($nodes = $this->getAttribute('maxesPerPage') as $maxPerPage) {
            $compiler->write($maxPerPage.",\n");
        }
        $compiler->outdent();
        $compiler->write(");\n");

        $compiler
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
