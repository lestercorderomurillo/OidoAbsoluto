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

            $compiledTokens = Collection::tokenize($this->using, $this->from);

            foreach ($compiledTokens as $tokens) {

                $html .= $this->renderChilds(Collection::mergeDictionary($tokens, ['random' => Cryptography::computeRandomKey(8)]));
            }
        }


        return $html;
    }
}

publish(ForeachComponent::class);
