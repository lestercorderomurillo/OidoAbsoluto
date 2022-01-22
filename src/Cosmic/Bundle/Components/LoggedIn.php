<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Authorization;
use Cosmic\Binder\Component;

class LoggedIn extends Component
{
    public function __construct(string $role = "*")
    {
        switch (strtolower($role)){
            case "user": $this->role = Authorization::USER; break;
            case "admin": $this->role = Authorization::ADMIN; break;
            default: $this->role = "*"; break;
        }
    }

    public function render()
    {
        if (Authorization::isLogged()) {

            if ($this->role === "*") {

                return <<<HTML
                    {body}
                HTML;

            } else {

                if (Authorization::getCurrentRole() == (int)$this->role) {

                    return <<<HTML
                        {body}
                    HTML;
                    
                }else{

                    $this->body = "";
                    return __EMPTY__;

                }
            }
        }
    }
}

publish(LoggedIn::class);