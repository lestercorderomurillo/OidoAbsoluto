<?php
return
[
    "survey" => [
        "required" => ["source"],
        "defaults" => ["skip" => 0, "take" => 10000],
        "inline",
        "render" => 
        <<<HTML
        <foreach name="question" from="{this:source}" skip="{this:skip}" take="{this:take}">
            <app:card padding="3">
                {question:id} . {question:subject}
                <if value="{question:type}" equals="text">
                    <app:textfield bind="q-{question:id}">
                </if>
                <if value="{question:type}" equals="number">
                    <app:textfield bind="q-{question:id}" type="number">
                </if>
                <if value="{question:type}" equals="choice-single">
                    <app:select bind="q-{question:id}" arrayName="question:choices">
                </if>
                <if value="{question:type}" equals="choice-multiple">
                    <br>
                    <foreach name="choice" from="{question:choices}">
                        <app:radio bind="q-{question:id}" id="{choice}" value="{choice}" text="{choice}">
                    </foreach>
                </if>
            </app:card>
        </foreach>
        HTML
    ]
];