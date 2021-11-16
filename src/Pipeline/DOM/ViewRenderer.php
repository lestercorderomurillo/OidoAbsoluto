<?php

namespace Pipeline\DOM;

use Pipeline\Core\DI;
use Pipeline\Core\Types\View;
use Pipeline\DOM\HTML\BodyBeautifier;
use Pipeline\Utilities\Pattern;

class ViewRenderer
{
    private View $view;

    public function render(): string
    {
        $pypeDOM = DI::getDependency(PypeDOM::class);
        $pypeDOM->loadView($this->view);

        $replacers = [
            "view:components" => $pypeDOM->getComponentScriptsOutputHTML(),
            "view:stateful" => $pypeDOM->getStatefulScriptsOutputHTML(),
            "view:awake" => $pypeDOM->getAwakeScriptsOutputHTML(),
        ];

        Compiler::setPypeDOM($pypeDOM);
        
        $output = Compiler::renderString($this->view->getSourceHTML());
        $output = Pattern::substituteTokens($output, $replacers);

        return (new BodyBeautifier())->beautifyString($output);
    }

    public function &setView(View $view): ViewRenderer
    {
        $this->view = $view;
        return $this;
    }

    public function getView(): View
    {
        return $this->view;
    }
}
