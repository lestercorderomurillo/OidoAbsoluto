<?php

namespace Pipeline\PypeEngine;

use Pipeline\PypeEngine\Inproc\HTMLBeautifier;
use Pipeline\PypeEngine\Inproc\HTMLStrip;
use Pipeline\PypeEngine\Inproc\Selection;
use Pipeline\Utilities\PatternHelper;
use Pipeline\Utilities\StringHelper;
use Pipeline\Traits\DefaultAccessorTrait;

class PypeViewRenderer
{
    use DefaultAccessorTrait;

    private View $view;
    private PypeTemplateBatch $batch;

    static int $auto_id = 0;

    public function render(): string
    {
        PypeCompiler::setContextFactory(new PypeContextFactory($this->batch, $this->view));
        $output = PypeCompiler::renderString($this->view->getSourceHTML());

        $output = str_replace("{view:components}", PypeCompiler::$context_factory->getCompiledComponentScripts(), $output); 
        $output = str_replace("{view:stateful}", PypeCompiler::$context_factory->getCompiledStatefulScripts(), $output); 
        $output = str_replace("{view:awake}", PypeCompiler::$context_factory->getCompiledAwakeScripts(), $output); 

        $html_beautifyr = new HTMLBeautifier();
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

    public function &setTemplateBatch(PypeTemplateBatch $batch): PypeViewRenderer
    {
        $this->batch = $batch;
        return $this;
    }

    public function &getTemplateBatch(): PypeTemplateBatch
    {
        return $this->batch;
    }
}
