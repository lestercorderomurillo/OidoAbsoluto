<?php

namespace Pipeline\PypeEngine;

use Pipeline\Core\View;
use Pipeline\PypeEngine\HTML\BodyBeautifier;
use Pipeline\Traits\DefaultAccessorTrait;

class PypeViewRenderer
{
    use DefaultAccessorTrait;

    private View $view;

    static int $auto_id = 0;

    public function render(): string
    {
        PypeCompiler::setContextFactory(new PypeContextFactory($this->view));
        $output = PypeCompiler::renderString($this->view->getSourceHTML());

        $output = str_replace("{view:components}", PypeCompiler::$context_factory->getCompiledComponentScripts(), $output); 
        $output = str_replace("{view:stateful}", PypeCompiler::$context_factory->getCompiledStatefulScripts(), $output); 
        $output = str_replace("{view:awake}", PypeCompiler::$context_factory->getCompiledAwakeScripts(), $output); 

        $html_beautifyr = new BodyBeautifier();
        return $html_beautifyr->beautifyString($output);
    }

    public function &setView(View $view): PypeViewRenderer
    {
        $this->view = $view;
        return $this;
    }

    public function getView(): View
    {
        return $this->view;
    }
}
