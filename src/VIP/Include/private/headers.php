<?php

use VIP\FileSystem\DirectoryPath;
use VIP\FileSystem\WebDirectory;

$public = (new DirectoryPath(DirectoryPath::DIR_PUBLIC))->toWebDirectory()->toString(); 
$web = (new WebDirectory("web/"))->toString(); ?>

<link rel='preconnect' href='https://fonts.gstatic.com'>
<link href='https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100&display=swap' rel='stylesheet'>

<script type="text/javascript"> const __URL__ = "<?=__URL__?>";</script>
<script type="text/javascript" src='<?=$public."jquery-3.6.0/jquery.min.js"?>'></script>
<script type="text/javascript" src='<?=$public."popper-1.16.1/popper.min.js"?>'></script>
<script type="text/javascript" src='<?=$public."bootstrap-4.6.0/bootstrap.min.js"?>'></script>
<script type="text/javascript" src='<?=$public."observable-slim-0.1.5/observable-slim.min.js"?>'></script>
<script type="text/javascript" src='<?=$public."jquery-validate-1.11.1/jquery.validate.min.js"?>'></script>

<link href='<?=$public."bootstrap-4.6.0/bootstrap.css"?>' rel="stylesheet">
<link href='<?=$public."font-awesome-4.7.0/font-awesome.css"?>' rel="stylesheet">

<link rel='stylesheet' href="<?=$web."css/compiled"?>">
<script type="text/javascript"><?=require_once("kernel.js")?></script>
