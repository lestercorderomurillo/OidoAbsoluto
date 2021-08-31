<?php

use function Pipeline\Accessors\Session;

return
    [
        "form" => [
            "required" => ["route"],
            "defaults" => ["id" => "form", "method" => "post"],
            "prototype" => "form",
            "id" => "[id]",
            "include" => ["action" => "[@url][route]", "method" => "[method]", "autocomplete" => "[@random]"]
        ],
        "alert" => [
            "prototype" => "div",
            "defaults" => ["type" => "warning"],
            "class" => "alert alert-[type] alert-dismissible fade show",
            "concatElement" => [
                "before" => "<strong>Oops!</strong> [message]",
                "prototype" => "button",
                "class" => "close",
                "type" => "button",
                "include" => ["data-dismiss" => "alert", "aria-label" => "Close"],
                "concatElement" => [
                    "prototype" => "span",
                    "content" => "<small>&times;<small>",
                    "closure"
                ],
                "closure",
                "new-line"
            ],
            "closure"
        ],
        "span" => [
            "prototype" => "label",
            "class" => "v-label w-100 text-color pb-2 text-[textPosition] [classList]",
            "style" => "font-size:[fontSize];",
            "defaults" => ["fontSize" => "12px"]
        ],
        "label" => [
            "required" => ["for"],
            "prototype" => "label",
            "class" => "v-label w-100 text-color",
            "include" => ["for" => "[for]"]
        ],
        "hint" => [
            "prototype" => "label",
            "class" => "small"
        ],
        "textbox" => [
            "required" => ["bind"],
            "defaults" => ["maxWidth" => "1200px", "autoComplete" => "[@random]"],
            "prototype" => "input",
            "id" => "[bind]",
            "name" => "[bind]",
            "type" => "text",
            "class" => "form-control v-textbox v-focuseable",
            "style" => "max-width: [maxWidth];",
            "include" => ["maxlength" => "64", "autocomplete" => "[autoComplete]"],
            "new-line"
        ],
        "password" => [
            "prototype" => "input",
            "id" => "[bind]",
            "name" => "[bind]",
            "type" => "password",
            "class" => "form-control v-textbox v-focuseable",
            "style" => "max-width: [maxWidth];",
            "defaults" => ["maxWidth" => "1200px", "autoComplete" => "[@random]", "bind" => "password"],
            "include" => ["maxlength" => "64", "autocomplete" => "[autoComplete]"],
            "new-line"
        ],
        "select" => [
            "required" => ["bind", "arrayName"],
            "prototype" => "select",
            "id" => "[bind]",
            "name" => "[bind]",
            "class" => "form-control v-textbox",
            "concatElement" => [
                "function-parameters" => [
                    "array_name" => "[arrayName]"
                ],
                "function" => function ($view_data, $params) {
                    $content = "<option disabled selected>Seleccione una opción...</option>\n";
                    if (isset($params["array_name"])) {
                        $array_name = $params["array_name"];
                        if (isset($view_data["$array_name"])) {
                            foreach ($view_data["$array_name"] as $key => $value) {
                                $content .= "<option value=\"" . $key . "\">" . $value . "</option>\n";
                            }
                            return $content;
                        }
                    }
                    return NULL;
                }
            ],
            "closure",
            "new-line"
        ],
        "date" => [
            "required" => ["bind"],
            "defaults" => ["_template_class" => "form-control d-inline v-textbox v-focuseable text-small", "_template_separator" => " / "],
            "prototype" => "input",
            "id" => "[bind]",
            "name" => "[bind]",
            "type" => "hidden",
            "concatElement" => [
                "prototype" => "input",
                "id" => "[bind]_dd",
                "name" => "[bind]_dd",
                "class" => "[_template_class]",
                "style" => "max-width: 50px;",
                "include" => ["maxlength" => "2", "placeholder" => "DD"],
                "concatElement" => [
                    "before" => "[_template_separator]",
                    "prototype" => "input",
                    "id" => "[bind]_mm",
                    "name" => "[bind]_mm",
                    "class" => "[_template_class]",
                    "style" => "max-width: 50px;",
                    "include" => ["maxlength" => "2", "placeholder" => "MM"],
                    "concatElement" => [
                        "before" => "[_template_separator]",
                        "prototype" => "input",
                        "id" => "[bind]_yy",
                        "name" => "[bind]_yy",
                        "class" => "[_template_class]",
                        "style" => "max-width: 60px;",
                        "include" => ["maxlength" => "4", "placeholder" => "YYYY"],
                        "new-line"
                    ]
                ]
            ]
        ],
        "radio" => [
            "required" => ["bind", "value", "text"],
            "prototype" => "input",
            "id" => "[bind]-[@counter]",
            "name" => "[bind]",
            "type" => "radio",
            "class" => "v-radio p-1 ml-1",
            "include" => ["value" => "[value]"],
            "concatElement" => [
                "prototype" => "label",
                "class" => "pl-2",
                "include" => ["for" => "[bind]-[@counter]"],
                "content" => "[text]",
                "closure",
                "new-line"
            ]
        ],
        "error" => [
            "defaults" => ["text" => ""],
            "prototype" => "label",
            "id" => "error_[for]",
            "class" => "error",
            "include" => ["for" => "[for]"],
            "content" => "[text]",
            "closure",
            "new-line"
        ],
        "persistent-message" => [
            "function-parameters" => [
                "message" => Session("message"),
                "message-type" => Session("message-type")
            ],
            "function" => function ($view_data, $params) {
                if (strlen($params["message"]) > 0) {
                    $message = $params["message"];
                    $type = isset($params["message-type"]) ? $params["message-type"] : "info";
                    $content = "<v-alert message='$message' type='$type'>\n";
                    Session()->remove("message");
                    Session()->remove("message-type");
                    return $content;
                }
                return "";
            }
        ],
        "fatal" => [
            "prototype" => "div",
            "class" => "v-fatal"
        ]
    ];
