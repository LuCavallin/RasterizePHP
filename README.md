# RasterizePHP
PDF generation class using Ariya Hidayat's PhantomJS and rasterize.js.
Simply pass some HTML or an URL to the class and then call the *rasterize()* method to get the converted file.
If an URL is provided, content will be automatically retrieved from the page.
Still in development, please feel free to download and contribute!

Brief example (same of the attached example.php):


    use LuCavallin\RasterizePHP\RasterizePHP as RasterizePHP;

    $rasterizePHP = new RasterizePHP("https://google.com/");

    $pdf = $rasterizePHP->rasterize();

    header("Content-type:application/pdf");
    header("Content-Disposition:attachment;filename='RasterizePHP.pdf'");

    readfile($pdf);


