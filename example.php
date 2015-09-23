<?php
    require 'vendor/autoload.php';

    use LuCavallin\RasterizePHP\RasterizePHP as RasterizePHP;

    $rasterizePHP = new RasterizePHP("https://google.com/");

    $pdf = $rasterizePHP->rasterize();

    header("Content-type:application/pdf");
    header("Content-Disposition:attachment;filename='RasterizePHP.pdf'");

    readfile($pdf);
