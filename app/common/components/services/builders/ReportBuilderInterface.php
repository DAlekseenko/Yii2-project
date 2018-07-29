<?php

namespace common\components\services\builders;

/**
 * Interface ReportBuilderInterface
 * @package common\components\services\builders
 */
interface ReportBuilderInterface
{
    /**
     * Подготовим строчки
     * @param $tr
     * @return mixed
     */
    public function prepareRow($tr);

    /**
     * Подготовим шапку таблицы
     * @return mixed
     */
    public function prepareHeader();

    /**
     * Получить данные для отчета
     * @return mixed
     */
    public function getData();
}