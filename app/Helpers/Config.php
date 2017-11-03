<?php
namespace App\Helpers;

class Config
{
    protected $configs = null;

    private function __construct()
    {
        $this->configs = json_decode(file_get_contents(__DIR__ . "/../../config/config.json"));
    }

    private function __clone()
    {
    }

    public static function instance()
    {
        return new self;
    }

    public function __get($name)
    {
        return isset($this->configs->$name) ? $this->configs->$name : null;
    }
}