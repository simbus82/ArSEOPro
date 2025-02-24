<?php

class ArSeoProLogger
{
    protected static $_instance;
    protected $f = null;
    protected $fileOpened = false;

    /**
     * 
     * @return ArSeoProLogger
     */
    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function log($message = null)
    {
        $msg = $message;
        if (is_array($message) || is_object($message)) {
            $msg = print_r($message, true);
        }
        if (!$this->fileOpened) {
            $this->openFile();
        }
        if (empty($msg)) {
            fwrite($this->f, PHP_EOL);
        } else {
            fwrite($this->f, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL);
        }
    }
    
    protected function __construct()
    {
        $this->openFile();
    }
    
    protected function openFile()
    {
        if ($this->f = fopen(dirname(__FILE__) . '/../' . 'arseopro.log', 'a+')) {
            $this->fileOpened = true;
        }
    }

    protected function closeFile()
    {
        if (!empty($this->f)) {
            fclose($this->f);
        }
        $this->fileOpened = false;
    }

    public function __destruct()
    {
        $this->closeFile();
    }
}
