<?php

namespace VIP\Renderer;

use VIP\Service\AbstractService;
use VIP\Core\InstanceLoaderInterface;
use ScssPhp\ScssPhp\Compiler as SCSSCompiler;

class PreProcessor extends AbstractService implements InstanceLoaderInterface
{
    private bool $recompile = false;

    public function __construct($recompile = true)
    {
        parent::__construct("PreProcessor");
        $this->recompile = $recompile;
    }

    public function onInstanceLoad(): void
    {
        $this->execute();
    }

    public function execute()
    {
        if ($this->recompile == true) {
            $page_scss_folder = __LWEB__;
            $prefab_scss_folder = __RDIR__ . "/src/VIP/Include/style/";
            $scss_folders = [$prefab_scss_folder, $page_scss_folder];
            $output_folder = __LWEB__."css/";
            $scss_compiler = new SCSSCompiler();
            $scss_compiler->setImportPaths($scss_folders);
            if (!file_exists($output_folder)) {
                mkdir($output_folder, 0777, true);
            }

            for ($i = 0; $i < count($scss_folders); $i++) {

                $files = glob($scss_folders[$i] . "*.scss");
                foreach ($files as $file_path) {
                    $file_path_elements = pathinfo($file_path);
                    $file_name = $file_path_elements['filename'];
                    $string_scss = file_get_contents($scss_folders[$i] . $file_name . ".scss");
                    $string_css = $scss_compiler->compile($string_scss);
                    $output = $output_folder . $file_name . ".css";
                    file_put_contents($output, $string_css);
                }
            }
        }
    }
}
