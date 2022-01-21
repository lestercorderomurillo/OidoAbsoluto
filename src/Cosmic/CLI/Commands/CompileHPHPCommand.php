<?php

namespace Cosmic\CLI\Commands;

use Cosmic\CLI\Abstracts\Command;
use Cosmic\FileSystem\FS;
use Cosmic\FileSystem\Paths\FilePath;
use Cosmic\HPHP\Compiler;
use Psr\Log\LogLevel;

class CompileHPHPCommand extends Command
{
    const categoryName = "compile";
    const commandName = "all";

    private $compiler;

    const errors = [
        "Must be a valid controller name."
    ];

    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    public function verifyArguments(array $args)
    {
        return true;
    }

    public function execute(...$args): int
    {
        return $this->call(...$args);
    }

    public function call(): int
    {
        $files = FS::find("app/Views/hphp/", "hphp");

        foreach ($files as $file) {

            //if (!FS::exists(new FilePath($file . "-c"))) {
                // compile now
                $out = $this->compiler->compileHPHPFile($file);
                $file->setExtension("php");
                $file = $file->toString();
                FS::write($file, "<?php" . $out, 0);

            //}
        }

        /*$file = new FilePath("app/Controllers/$controllerName" . "Controller.php");

        if (!FS::exists($file)) {
            FS::write($file, $output);
            cout("Controller $controllerName has been created successfully.", [], LogLevel::INFO);
        } else {
            cout("Controller already exists or not enough write permission.", [], LogLevel::ERROR);
        }*/


        return 1;
    }
}
