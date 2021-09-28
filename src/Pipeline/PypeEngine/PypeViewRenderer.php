<?php

namespace Pipeline\PypeEngine;

class PypeViewRenderer
{
    private View $view;

    public function render(): string
    {
        return PypeCompiler::renderString($this->view->getSourceHTML(), $this->view->getContext());
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
