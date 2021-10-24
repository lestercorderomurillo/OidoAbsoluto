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
        ],
        "inline",
        "render" => 
        <<<HTML
        <foreach name="question" from="{this:source}" skip="{this:skip}" take="{this:take}">
            <app:card padding="3" minWidth="{this:minWidth}" minHeight="{this:minHeight}">
                {question:id} . {question:subject}
                <if value="{question:type}" equals="text">
                    <app:textfield 
                    bind="q-{question:id}" 
                    requiredId="{question:require-id}" 
                    requiredValue="{question:require-value}"
                    requiredPattern="q-">
                </if>
                <if value="{question:type}" equals="number">
                    <app:textfield 
                    bind="q-{question:id}" 
                    type="number" requiredId="{question:require-id}" 
                    requiredValue="{question:require-value}"
                    requiredPattern="q-">
                </if>
                <if value="{question:type}" equals="choice-single">
                    <app:select 
                    bind="q-{question:id}" 
                    arrayName="question:choices" 
                    requiredId="{question:require-id}" 
                    requiredValue="{question:require-value}"
                    requiredPattern="q-">
                </if>
                <if value="{question:type}" equals="choice-multiple">
                    <br>
                    <foreach name="choice" from="{question:choices}">
                        <app:radio 
                        bind="q-{question:id}" 
                        id="{choice}" 
                        value="{choice}" 
                        text="{choice}" 
                        requiredId="{question:require-id}" 
                        requiredValue="{question:require-value}"
                        requiredPattern="q-">
                    </foreach>
                </if>
            </app:card>
        </foreach>
        HTML
    ]
];