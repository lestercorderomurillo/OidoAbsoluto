<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Cryptography;
use function Cosmic\Core\Bootstrap\publish;

class ForComponent extends Component
{
    const Name = "For";

    public function __construct(string $from, string $to, string $using = "iterator")
    {
        $this->from = $from;
        $this->to = $to;
        $this->using = $using;
    }

    public function render(): string
    {
        $html = __EMPTY__;
        
        $step = $this->to - $this->from;

        if($step >= 0){

            for($i = 0; $i < $step; $i++)
            {
                $html .= $this->renderChilds([$this->using => $i, 'random' => Cryptography::computeRandomKey(8)]);
            }

        }else if($step < 0){

            for($i = $step; $i > 0; $i--)
            {
                $html .= $this->renderChilds([$this->using => $i, 'random' => Cryptography::computeRandomKey(8)]);
            }

        }

        return $html;

    }
}

class ForeachComponent extends Component
{
    const Name = "Foreach";

    public function __construct(array $from, string $using = "iterator")
    {
        $this->from = $from;
        $this->using = $using;
    }

    private function createTokens(string $base, $arrays)
    {
        $tokens = [];

        foreach ($arrays as $array) {
            $tokens[] = $this->createTokensRecursive($base, $array);
        }

        return $tokens;
    }

    private function createTokensRecursive(string $base, $data)
    {
        $tokens = [];
        $tokens[$base] = "array";

        foreach ($data as $key => $value) {

            $tokenName = "$base.$key";

            if (is_string(($value))) {

                $tokens[$tokenName] = $value;
                
            } else if (is_array($value)) {

                $recursiveTokens = $this->createTokensRecursive($tokenName, $value);
                $tokens = Collection::mergeDictionary(
                    $tokens,
                    $recursiveTokens
                );

                $tokens[$tokenName] = "array";
            }
        }

        return $tokens;
    }


    public function render(): string
    {
        $html = __EMPTY__;

        if (!Collection::is2Dimensional($this->from)) {

            foreach ($this->from as $token) {

                $html .= $this->renderChilds(Collection::mergeDictionary(
                    [$this->using => $token],
                    ['random' => Cryptography::computeRandomKey(8)]
                ));
            }
        } else {

            $compiledTokens = $this->createTokens($this->using, $this->from);

            foreach ($compiledTokens as $tokens) {

                $html .= $this->renderChilds(Collection::mergeDictionary($tokens, ['random' => Cryptography::computeRandomKey(8)]));
            }
        }


        return $html;
    }
}

publish([ForComponent::class, ForeachComponent::class]);
