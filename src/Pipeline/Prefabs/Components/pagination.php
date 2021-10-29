<?php
return
[
    "paginator" => [
        "required" => [
            "numberOfPages", 
            "id"
        ],
        "defaults" => [
            "elementsPerPage" => 10, 
            "currentPage" => 0,
            "prevPageText" => "Página anterior",
            "nextPageText" => "Página siguiente",
            "lastPageText" => "Concluir",
            "lastPageKey" => "last-page-key"
        ],
        "stateful" => [
            "currentPage", 
            "numberOfPages"
        ],
        "render" => 
        <<<HTML
        <this id="{this:id}">
            <for start="0" end="({this:numberOfPages}-1)">
                <app:paginator-page page="{i}">
                    {this:body}
                </app:paginator-page>
            </for>
        </this>
        <br>
        <app:script-button onclick="{this}_prevPage('{this:id}')">{this:prevPageText}</app:script-button>
        <app:script-button 
        onclick="{this}_nextPage('{this:id}')" 
        id="{this:id}-{this:lastPageKey}"
        lastPageKey="{this:lastPageKey}"
        lastPageText="{this:lastPageText}"
        nextPageText="{this:nextPageText}">
            {this:nextPageText}
        </app:script-button>
        HTML,
        "awake" => <<<JS
            {this}_setPage("{this:id}", 0);
            $("#{this:id}-{this:lastPageKey}").html("{this:nextPageText}");
        JS,
        "scripts" =>
        <<<JS
        function prevPage(id){
            this.setPage(id, this.state("currentPage") - 1);
        }
        function nextPage(id, lastPageKey, lastPageText, nextPageText){
            this.setPage(id, this.state("currentPage") + 1);
            if(this.state("currentPage") == (this.state("numberOfPages") - 1)){
                $("#" + id + "-" + lastPageKey).html(lastPageText);
            }else{
                $("#" + id + "-" + lastPageKey).html(nextPageText);
            }
        }
        function setPage(id, currentPage){
            this.state("currentPage", currentPage.clamp(0, this.state("numberOfPages") - 1));
            $(".app-paginator-page").hide();
            $(".app-paginator-page[page=\"" + this.state("currentPage") + "\"]").show();
        }
        JS
    ],
    "paginator-page" => [
        "required" => ["page"],
        "render" => 
        <<<HTML
        <this class="hide" page="{this:page}">
            {this:body}
        </this>
        HTML
    ]
];