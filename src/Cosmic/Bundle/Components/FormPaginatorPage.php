<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class FormPaginatorPage extends Component
{
    public function __construct(int $page, string $paginator)
    {
        $this->page = $page;
        $this->paginator = $paginator;
    }

    public function render()
    {
        $this->compiledBody = $this->renderChilds(["page" => $this->page]);

        return <<<HTML
            <div paginator="{paginator}" page="{page}" class="FormPaginatorPage hide">
                {compiledBody}
            </div>
        HTML;
    }
}

publish(FormPaginatorPage::class);
