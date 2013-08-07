<?php

namespace Carew\Twig\Node;

class Pagination extends \Twig_Node
{
    public function __construct(\Twig_Node $node, $maxPerPage)
    {
        parent::__construct(array('node' => $node), array('maxPerPage' => (integer) $maxPerPage));
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $this->compileGetNbItems($compiler);

        $this->compileMaxPerPage($compiler);
    }

    private function compileGetNbItems(\Twig_Compiler $compiler)
    {
        $compiler
            ->write("public function getNbItems(array \$context)\n", "{\n")
            ->indent()
        ;

        $compiler->addIndentation();
        $compiler->raw("\$context = \$this->env->mergeGlobals(\$context);\n\n", false);
        $compiler->addIndentation();
        $compiler->raw('return count(', false);
        $compiler->subcompile($this->getNode('node'));
        $compiler->raw(");\n");

        $compiler
            ->outdent()
            ->write("}\n\n")
        ;
    }

    private function compileMaxPerPage(\Twig_Compiler $compiler)
    {
        $compiler
            ->write("public function getMaxPerPage()\n", "{\n")
            ->indent()
        ;

        $compiler->write(sprintf("return %s;\n", $this->getAttribute('maxPerPage')));

        $compiler
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
