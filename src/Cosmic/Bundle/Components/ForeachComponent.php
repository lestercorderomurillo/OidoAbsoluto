<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Cryptography;
use Cosmic\Utilities\Transport;

class ForeachComponent extends Component
{
    const Name = "Foreach";

    public function __construct(array $from, string $using = "iterator", int $skip = 0, int $take = 10000)
    {
        $this->from = array_slice($from, $skip, $take);
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

        foreach ($data as $key => $value) {

            $tokenName = "$base.$key";

            if (is_string($value) || is_int($value) || is_float($value)) {

                $tokens[$tokenName] = $value;

            } else if (is_array($value)) {

                $recursiveTokens = $this->createTokensRecursive($tokenName, $value);
                $tokens = Collection::mergeDictionary(
                    $tokens,
                    $recursiveTokens
                );

                $tokens[$tokenName] = Transport::arrayToString($value);
            }
        }

        return $tokens;
    }


    public function render()
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

publish(ForeachComponent::class);