<?php
/**
 * User: buchatskiy
 * Date: 19.04.2017
 * Time: 8:22
 */

namespace common\components\services;


class TemplateHelper
{
    /**
     * Вхождения типа {имя_переменной} заменяются на значения этой переменной если она присутсвует в списке замен.
     * Если замена не найдена шаблонная вставка просто удаляется.
     *
     * @param array $templates массив шаблонов которых нужно заполнить
     * @param array $replacements список замен. Массив заполняемых занчений, вида: имя_переменной => значение_переменной
     * @return array
     */
    public static function fillTemplates(array $templates, array $replacements)
    {
        foreach ($templates as &$template) {
            $template = preg_replace_callback('/\{([^{}]+)\}/i', function($matches) use($replacements) {
                return isset($replacements[$matches[1]]) ? $replacements[$matches[1]] : '';
            }, $template);
        }
        return $templates;
    }
}