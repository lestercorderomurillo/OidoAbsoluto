<?php

namespace Cosmic\DOM;

use Cosmic\Core\DI;
use Cosmic\Core\Types\View;
use Cosmic\DOM\HTML\BodyBeautifier;
use Cosmic\Utilities\Pattern;

use function Cosmic\Kernel\app;

class ViewRenderer
{
    private View $view;

    public function render(): string
    {
        $pypeDOM = app()->get(PypeDOM::class);
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
