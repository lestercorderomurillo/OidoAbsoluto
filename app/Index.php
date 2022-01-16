<?php

define('__EMPTY__', '');
define('__EXPERIMENTAL__', true);
define('__VERSION__', '1.0');

require_once(dirname(__DIR__) . "/vendor/autoload.php");
require_once(dirname(__DIR__) . "/src/Cosmic/Core/Kernel.php");

use App\Providers\OAProviders;
use Cosmic\Core\Applications\ConsoleApplication;
use Cosmic\Core\Applications\MVCApplication;

class OidoAbsolutoApplication extends MVCApplication
{
    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        parent::boot();
        OAProviders::default();
    }

    /**
     * @inheritdoc
     */
    public function dispose(): void
    {
        parent::dispose();
    }
}

$exitCode = 0;

if(!defined('__CONSOLE__')){
    define('__CONSOLE__', false);
    $exitCode = __SYSCALL__(OidoAbsolutoApplication::class);
}else{
    $exitCode = __SYSCALL__(ConsoleApplication::class, [$argv]);
}

exit($exitCode);