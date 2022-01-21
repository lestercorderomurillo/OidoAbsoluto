<?php

namespace App\Components;

use Cosmic\HPHP\Node;
use Cosmic\HPHP\Component;

class MyComponent extends Component
{ 
    private float $type = null;

    public function __construct(string $name, array $from)
    {
        $this->name = $name;
        $this->from = $from;
    }

    public function render()
    {
        return new Node('div', [], [])
            new Node('Component', [], [])
               $iterator('Foreach', [], [])from} using={$iterator}>
                    <Component>$iterator</Component>
                </Foreach>
            </Component>
        </div>;
    }
}