<?php
/**
 * Created by Eugene.
 * User: eugene
 * Date: 27/08/18
 * Time: 14:54
 */

include_once 'ImportCategories.php';

/**
 * Class Import - импорт данных из 1С.
 */
class Import
{
    /**
     * @var ImportCategories $importCategories
     */
    private $importCategories;

    /**
     * Import constructor.
     */
    public function __construct() {
        $this->importCategories = new ImportCategories();
    }

    /**
     * Исходя из GET параметров, выполнить импорт.
     *
     * @return void
     */
    public function fetch()
    {
        // Попытка импорта.
        try {
            $this->importCategories->process();
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}

$obj = new Import();
$obj->fetch();