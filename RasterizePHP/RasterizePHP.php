<?php namespace LuCavallin\RasterizePHP;

/**
 * Class RasterizePHP
 * @package LuCavallin\RasterizePHP
 *
 * RasterizePHP uses Ariya Hidayat's PhantomJS and rasterize.js to convert HTML to PDF and other formats.
 * If an URL is set as input, RasterizePHP will automagically retrieve contents from web page (allow_url_fopen must be enabled).
 * A local HTML file is always used as source for conversion (path is customizable).
 *
 * RasterizePHP ships with PhantomJS for Mac OS X. Replace with the version you need.
 *
 * See http://phantomjs.org/ or https://github.com/ariya/phantomjs/blob/master/examples/rasterize.js or http://phantomjs.org/screen-capture.html
 * for further details on PhantomJS.
 *
 * RasterizePHP by Luca Cavallin © 2015
 * Web: http://lucavall.in
 * Email: me@lucavall.in
 * Github: https://github.com/LuCavallin
 * StackOverflow: http://stackoverflow.com/users/5347655/lucavallin
 * LinkedIn: https://linkedin.com/in/lucavallin
 */
class RasterizePHP
{
    /**
     * Necessary to PhantomJS for reaching local source file
     */
    const FILE_URI = "file://";
    /**
     * Default directories
     */
    const DEFAULT_DIR = "tmp/rasterizephp/";
    /**
     * Local source file extension, must be ".html"
     */
    const SRC_EXTENSION = ".html";
    /**
     * @var string
     *
     * Temporary filename
     */
    public $filename;
    /**
     * @var
     *
     * PhantomJS + rasterize.js path, used in conversion command
     */
    private $scriptPath;
    /**
     * @var array
     *
     * rasterize.js allowed extensions
     */
    private $allowedExtensions = ["png", "jpg", "jpeg", "gif", "pdf"];
    /**
     * @var
     *
     * Local HTML source file path
     */
    private $srcPath;
    /**
     * @var
     *
     * Destination extension, must be one of the allowed extensions
     */
    private $dstExtension;
    /**
     * @var
     *
     * Destination file path
     */
    private $dstPath;
    /**
     * @var
     *
     * Page size of converted file
     * Examples for PDF output: "5in*7.5in", "10cm*20cm", "A4", "Letter"
     * Examples for Image output: "1920px" returns entire page (window width 1920px), "800px*600px" returns window clipped to 800x600
     */
    private $pageSize;
    /**
     * @var
     *
     * Input to convert, must be HTML or an URL
     */
    private $input;

    /**
     * @param $input
     * @param string $dstExtension
     * @param string $pageSize
     * @param string $srcPath
     * @param string $dstPath
     * @throws RasterizePHPException
     *
     * Initializes input and defaults
     */
    public function __construct($input, $dstExtension = "pdf", $pageSize = "A4", $srcPath = "", $dstPath = "")
    {
        $this->setScriptPath();
        $this->filename = "rasterizephp_" . time();
        $this->setSrcPath($srcPath);
        $this->setDstExtension($dstExtension);
        $this->setDstPath($dstPath);
        $this->setInput($input);
        $this->setPageSize($pageSize);
    }

    /**
     * @throws RasterizePHPException
     *
     * Sets PhantomJS + rasterize.js path, used in conversion command
     * Throws RasterizePHPException if PhantomJS or rasterize.js are not found
     */
    private function setScriptPath()
    {
        $phantomjs_path = dirname(__FILE__) . "/phantomjs/phantomjs";
        $rasterizejs_path = dirname(__FILE__) . "/phantomjs/rasterize.js";
        if (file_exists($phantomjs_path)) {
            if (file_exists($rasterizejs_path)) {
                $this->scriptPath = $phantomjs_path . " " . $rasterizejs_path;
            } else {
                throw new RasterizePHPException("rasterize.js not found.");
            }
        } else {
            throw new RasterizePHPException("PhantomJS not found.");
        }
    }

    /**
     * Delete html source file on destruct
     */
    public function __destruct()
    {
        unlink($this->srcPath);
    }

    /**
     * @return mixed
     */
    public function getScriptPath()
    {
        return $this->scriptPath;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return mixed
     */
    public function getSrcPath()
    {
        return $this->srcPath;
    }

    /**
     * @param string $srcPath
     * @throws RasterizePHPException
     *
     * Sets position of the local HTML source file
     * If $srcPath is empty, DEFAULT_DIR is used
     * Throws RasterizePHPException if location is unreachable
     */
    public function setSrcPath($srcPath = "")
    {
        $default_path = !empty($srcPath) ? $srcPath : self::DEFAULT_DIR;
        if (!file_exists($default_path)) {
            if (!mkdir($default_path, 0755, true)) {
                throw new RasterizePHPException("Unable to create default source directory ({$default_path})");
            }
        }
        $this->srcPath = realpath($default_path) . "/" . $this->filename . self::SRC_EXTENSION;
    }

    /**
     * @return mixed
     */
    public function getDstExtension()
    {
        return $this->dstExtension;
    }

    /**
     * @param $dstExtension
     * @throws RasterizePHPException
     *
     * Sets destination file (output) extension
     * Throws RasterizePHPException if $dstExtension is not among allowed extensions
     */
    public function setDstExtension($dstExtension)
    {
        if (in_array(strtolower($dstExtension), $this->allowedExtensions)) {
            $this->dstExtension = "." . $dstExtension;
        } else {
            throw new RasterizePHPException("Extension not allowed (\"$dstExtension\").");
        }
    }

    /**
     * @return mixed
     */
    public function getDstPath()
    {
        return $this->dstPath;
    }

    /**
     * @param string $dstPath
     * @throws RasterizePHPException
     *
     * Sets destination file path
     * Throws RasterizePHPException if location is unreachable
     */
    public function setDstPath($dstPath = "")
    {
        if (empty($dstPath)) {
            if (!file_exists(self::DEFAULT_DIR)) {
                if (!mkdir(self::DEFAULT_DIR, 0755, true)) {
                    throw new RasterizePHPException("Unable to create default destination directory ({self::DEFAULT_DIR})");
                }
            }
            $dstPath = self::DEFAULT_DIR . $this->filename . $this->dstExtension;
        }
        $this->dstPath = $dstPath;
    }

    /**
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param $input
     *
     * Sets HTML input to convert and writes contents to local HTML source file.
     * If an URL is passed, HTML is then retrieved from web page
     */
    public function setInput($input)
    {
        if (!filter_var($input, FILTER_VALIDATE_URL) === false) {
            $input = file_get_contents($input);
        }
        $this->input = $input;
        file_put_contents($this->srcPath, $this->input);
    }

    /**
     * @return bool
     *
     * Executes conversion command.
     * Returns destination file path on success or false on fail
     */
    public function rasterize()
    {
        exec($this->getCommand(), $output, $return_var);
        if ($return_var == 0) {
            return $this->dstPath;
        } else {
            return false;
        }
    }

    /**
     * @return string
     *
     * Returns full conversion command for exec()
     */
    private function getCommand()
    {
        return escapeshellcmd(implode(" ",
            [$this->scriptPath, self::FILE_URI . $this->srcPath, $this->dstPath, $this->pageSize]));
    }

}