<?php
/**
 * Created by Eugene.
 * User: eugene
 * Date: 27/08/18
 * Time: 12:19
 */

include_once 'ImportBase.php';
include_once 'ImportCategoryHelper.php';

/**
 * Class ImportCategories - иморт категорий из 1С.
 */
class ImportCategories extends ImportBase
{
    /**
     * Имя файла импорта категорий.
     *
     * @var string IMPORT_FILE_PATH
     */
    const IMPORT_FILE_PATH = 'price/categories.csv';

    /**
     * Разделитель CSV файла.
     *
     * @var string CSV_DELIMITER
     */
    const CSV_DELIMITER = ',';

    /**
     * Вспомогательный класс для простых задач.
     *
     * @var ImportCategoryHelper $helper
     */
    private $helper;

    /**
     * ImportCategories constructor.
     */
    public function __construct()
    {
        parent::__construct(self::IMPORT_FILE_PATH, self::CSV_DELIMITER);

        $this->helper = new ImportCategoryHelper($this->simpla);
    }

    /**
     * Выполнить импорт.
     *
     * @return void
     * @throws Exception
     */
    public function process()
    {
        // Чтение файла импорта построчно.
        $this->read_csv(function ($line, $line_number) {

            // Запись полей из файла в новые объекты.
            $category = $this->helper->createCategoryFromLine($line);
            $tags     = $this->helper->createTagsFromLine($line);

            // Не импортировать категорию если не указан id.
            if (empty($category->id)) {
                return;
            }

            // Если категория есть в базе, то обновить ее. В противном случае создать.
            if ($this->simpla->categories->is_category_exists(array('id' => $category->id))) {

                // Не изменять эти данные у категории при обновении существующей.
                unset($category->url);
                unset($category->meta_title);
                unset($category->content_title);

                // Обновить категорию.
                $this->simpla->categories->update_category($category->id, $category);
            } else {
                // Создать категорию.
                $this->simpla->categories->add_category($category);
            }
        });
    }
}