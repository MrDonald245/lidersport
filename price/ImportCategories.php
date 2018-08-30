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
    const CSV_DELIMITER = ';';

    /**
     * @var array $map имена приходящих полей из 1С в выгрузке категорий.
     */
    private $map;

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

        $this->map    = require_once 'category_map.php';
        $this->helper = new ImportCategoryHelper($this->simpla, $this->map);
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

            // Если у текущей категории есть родители, то обновить или создать родительские категории.
            $cat_parent_id = 0;

            // Получить id родителя категории исходя из вложенности названия категория в файле выгрузки.
            $parent_cats = $this->helper->get_parent_categories_from_name($line[$this->map['name']]);
            if (!empty($parent_cats)) {
                $cat_parent_id = $this->helper->prepare_parent_categories($parent_cats);
            }

            $category->parent_id = $cat_parent_id;

            // Если категория есть в базе, то обновить ее. В противном случае создать.
            if ($this->simpla->categories->is_category_exists(array('id' => $category->id))) {

                // Не изменять эти данные у категории при обновении существующей.
                unset($category->url);
                unset($category->meta_title);
                unset($category->content_title);

                // Обновить категорию.
                $this->simpla->categories->update_category($category->id, $category);

                // Перезаписать теги
                $this->helper->rewrite_tags($category->id, $tags);
            } elseif ($this->simpla->categories->is_category_exists(array('name' => $category->name))) {
                $twink_id = $this->simpla->categories->get_category_id_by_name($category->name);
                $twink    = $this->simpla->categories->get_category($twink_id);

                // Если категория с одинаковым иминем находится в одной вложености, то пресвоить ей id новой.
                if ($twink->parent_id == $category->parent_id) {

                    // Сменить старый id категории на новый.
                    $this->simpla->db->query('UPDATE __categories
                                              SET id = ? 
                                              WHERE id = ?', $category->id, $twink_id);

                    // Всем дочерним категориям сменть ссылку на новый id категории.
                    $this->simpla->db->query('UPDATE __categories
                                              SET parent_id = ?
                                              WHERE parent_id = ?', $category->id, $twink_id);

                    // Добавить теги
                    $this->helper->rewrite_tags($category->id, $tags);
                }
            } else {
                // Создать категорию.
                $this->simpla->categories->add_category($category);

                // Добавить теги
                $this->helper->rewrite_tags($category->id, $tags);
            }
        });
    }
}