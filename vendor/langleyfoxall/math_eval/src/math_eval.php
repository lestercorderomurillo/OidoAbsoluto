<?php

use LangleyFoxall\MathEval\MathEvaluator;

function math_eval($expression, $variables = null)
{
    $evaluator = new MathEvaluator($expression, $variables);

    return $evaluator->evaluate();
}

