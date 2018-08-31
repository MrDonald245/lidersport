<?php
/**
 * Даный файл служит оболочкой для файла Import.php.
 *
 * Created by Eugene.
 * User: eugene
 * Date: 31/08/18
 * Time: 11:57
 */

error_reporting(E_ALL ^ E_NOTICE);

if ($_GET['lid_passwd'] != 'svsdefes84j') {
    die('qwwrrt');
}

/**
 * Подключение переписаного участка выгрузки категорий
 * вместе с нетронутым скриптом выгрузки вариантов товара.
 */
include_once 'Import.php';