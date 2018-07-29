<?php


namespace common\components\services\builders;


abstract class AbstractReportBuilder implements ReportBuilderInterface
{
    /**
     * Модель с параметрами поиска
     * @var $search object
     */
    protected $search;

    /**
     * Расширение файла
     * @var $extension string
     */
    protected $extension = '.csv';

    /**
     * Имя файла
     * @var $fileName string
     */
    protected $fileName;

    /**
     * Путь по умолчанию
     * @var $path string
     */
    protected $path = DATA_DIR . 'backend/files/';


    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getFilePath()
    {
        return $this->getPath() . $this->getFullFileName();
    }

    public function getFullFileName()
    {
        return $this->fileName . $this->extension;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getFile()
    {
        try {
            $f = fopen($this->getFilePath(), 'w');
            \fputcsv($f, $this->prepareHeader());

            $query = $this->getData();
            foreach ($query->batch(10) as $batch) {
                foreach ($batch as $tr) {
                    \fputcsv($f, $this->prepareRow($tr));
                }
                gc_collect_cycles();
            }
            \fclose($f);
            return $this->getFilePath();
        } catch (\Exception $e) {
            \yii::info('REPORT BUILD ERROR: ' . $e->getMessage());
            return false;
        }
    }

}