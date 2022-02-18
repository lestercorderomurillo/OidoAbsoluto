<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;
use Cosmic\Bundle\Common\Language;

class FormPaginator extends Component
{
    public function __construct(string $numberOfPages, string $form)
    {
        $this->numberOfPages = $numberOfPages;
        $this->prevPageText = Language::getString("paginator00");
        $this->nextPageText = Language::getString("paginator01");
        $this->lastPageText = Language::getString("paginator02");
        $this->prevButtonID = generateID();
        $this->nextButtonID = generateID();
        $this->form = $form;
    }

    public function scripts()
    {
        return <<<JS
        function awake(){
            component.currentPage = 0;
            component.currentPageUI = 1;
            component.update();
        }
        function goBack(){
            component.currentPage--;
            component.update();
        }
        function goNext(){
            component.currentPage++;
            component.update();
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        function update(){
            if(component.currentPage == $this->numberOfPages){
                $("#$this->form").submit();
            }
            component.currentPage = clamp(component.currentPage, 0, $this->numberOfPages - 1);
            component.currentPageUI = component.currentPage + 1;
            $('.FormPaginatorPage[paginator="$this->id"]').hide();
            $('.FormPaginatorPage[paginator="$this->id"][page="' + component.currentPage + '"]').show();
            if(component.currentPage == 0){
                $('#$this->prevButtonID').hide();
            }else{
                $('#$this->prevButtonID').show();
            }
            if(component.currentPage == ($this->numberOfPages - 1)){
                $('#$this->nextButtonID').html("$this->lastPageText");
            }else{
                $('#$this->nextButtonID').html("$this->nextPageText");
            }
            if(component.currentPage == ($this->numberOfPages - 1)){
                component.nextPageText = "$this->lastPageText";
            }else{
                component.nextPageText = "$this->nextPageText";
            }
        }
        JS;
    }

    public function render()
    {
        return <<<HTML
            <div (load)="awake();" id="{id}">
                <For from="0" to="{numberOfPages}">
                    <FormPaginatorPage paginator="{id}" page="{parent.iterator}">
                        {body}
                    </FormPaginatorPage>
                </For>
            </div>
            <Spacing>
            <Button (click)="goBack()" id="{prevButtonID}">
                {prevPageText}
            </Button>
            <Button (click)="goNext()" id="{nextButtonID}">
                {?nextPageText}
            </Button>
            <Label class="d-inline"> 
                {?currentPageUI} / {numberOfPages}
            </Label>
        HTML;
    }
}

publish(FormPaginator::class);
