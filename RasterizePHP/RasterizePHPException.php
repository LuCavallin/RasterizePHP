<?php namespace LuCavallin\RasterizePHP;

    use Exception;

    /**
     * Class RasterizePHPException
     * @package LuCavallin\RasterizePHP
     *
     * Custom exceptions class for RasterizePHP
     */
    class RasterizePHPException extends Exception {

        /**
         * @param string $message
         * @param int $code
         * @param Exception|null $previous
         */
        public function __construct($message, $code = 0, Exception $previous = null) {
            parent::__construct($message, $code, $previous);
        }

        /**
         * @return string
         */
        public function __toString() {
            return __CLASS__ . ": {$this->message}\n";
        }

    }