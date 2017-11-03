<?php
namespace App\Helpers;

class Logger
{
    public $fileHandle = null;

    private function __construct($fileName)
    {
        $this->fileHandle = fopen($fileName, 'a');
    }

    private function __clone()
    {

    }

    public static function getInstance($fileName)
    {
        return new self($fileName);
    }

    public function getLogger()
    {
        while (true) {
            fwrite($this->fileHandle, yield . "\n");
        }
    }
}