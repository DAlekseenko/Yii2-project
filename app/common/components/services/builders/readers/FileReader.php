<?php

namespace common\components\services\builders\readers;

/**
 * Class FileReader
 * @package common\components\services\builders\readers
 */
class FileReader
{
    public $fileName;
    public $file;

    public function __construct($file)
    {
        $this->file = $file;
        $this->setFileName();
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName = null)
    {
        if($fileName){
            $this->fileName = $fileName;
        }
        $pos = strrpos($this->file, '/');
        $this->fileName = substr($this->file, $pos + 1);
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getFile()
    {
       return $this->file;
    }

    public function getReadyCSV()
    {
        if (file_exists($this->file)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Description: File Transfer');
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header('Content-Disposition: attachment; filename=' . basename($this->fileName));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($this->file));
            // читаем файл и отправляем его пользователю
            if ($fd = fopen($this->file, 'r')) {
                while (!feof($fd)) {
                    print fread($fd, 1024);
                }
                fclose($fd);
            }
            exit;
        }
    }

}