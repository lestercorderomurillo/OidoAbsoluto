<?php

namespace Pipeline\PypeDOM;

use Pipeline\Core\Types\View;
use Pipeline\PypeDOM\HTML\BodyBeautifier;

class PypeViewRenderer
{
    private View $view;

    static int $auto_id = 0;

    public function render(): string
    {
        PypeCompiler::setPypeDOM(new PypeContextFactory($this->view));
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
