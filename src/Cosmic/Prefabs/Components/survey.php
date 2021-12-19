<?php
return
[
    "survey" => [
        "required" => ["source"],
        "defaults" => [
            "skip" => 0, 
            "take" => 10000,
            "minWidth" => "10px",
            "minHeight" => "125px",
            "showNumbers" => "false"
        ],
        "inline",
        "render" => 
        <<<HTML
        <foreach name="question" from="{this:source}" skip="{this:skip}" take="{this:take}">
            <app:card columnSize="12" id="q-{question:id}-card" padding="3" minWidth="{this:minWidth}" minHeight="{this:minHeight}">
                <if value="{this:showNumbers}" equals="true">
                    {question:id} .
                </if>
                {question:subject}
                <if value="{question:type}" equals="text">
                    <app:textfield 
                    bind="q-{question:id}"
                    container="q-{question:id}-card"
                    requiredId="q-{question:require-id}" 
                    requiredValue="{question:require-value}"
                    requiredPattern="q-">
                </if>
                <if value="{question:type}" equals="number">
                    <app:textfield 
                    bind="q-{question:id}"
                    container="q-{question:id}-card"
                    requiredId="q-{question:require-id}" 
                    requiredValue="{question:require-value}"
                    requiredPattern="q-">
                </if>
                <if value="{question:type}" equals="choice-single">
                    <app:select 
                    arrayName="question:choices" 
                    bind="q-{question:id}"
                    container="q-{question:id}-card"
                    requiredId="q-{question:require-id}" 
                    requiredValue="{question:require-value}"
                    requiredPattern="q-">
                </if>
                <if value="{question:type}" equals="choice-multiple">
                    <br>
                    <div id="q-{question:id}">
                        <foreach name="choice" from="{question:choices}">
                            <br>
                            <app:radio 
                            bind="q-{question:id}" 
                            id="{choice}" 
                            value="{choice}" 
                            text="{choice}" 
                            bind="q-{question:id}"
                            container="q-{question:id}-card"
                            requiredId="q-{question:require-id}" 
                            requiredValue="{question:require-value}"
                            requiredPattern="q-">
                        </foreach>
                    </div>
                </if>
            </app:card>
        </foreach>
        HTML
    ]
];