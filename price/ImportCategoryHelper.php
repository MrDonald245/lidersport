<?php
/**
 * Created by Eugene.
 * User: eugene
 * Date: 28/08/18
 * Time: 11:03
 */

/**
 * Class ImportCategoryHelper - класс помощник для ImportCategories
 */
class ImportCategoryHelper
{
    /**
     * @var array $map имена приходящих полей из 1С в выгрузке категорий.
     */
    private $map;

    /**
     * @var Simpla
     */
    private $simpla;

    /**
     * ImportCategoryHelper constructor.
     *
     * @param Simpla $simpla
     */
    public function __construct($simpla)
    {
        $this->map    = require_once 'category_map.php';
        $this->simpla = $simpla;
    }

    /**
     * Создать категорию из строки csv файла.
     *
     * @param array $line тело csv файла
     *
     * @return stdClass
     */
    public function createCategoryFromLine($line)
    {
        // Получить вложеность категорий в виде массива.
        $cat_nesting   = explode('/', $line[$this->map['name']]);
        $cat_name      = array_pop($cat_nesting); // Имя текущей категории.
        $cat_parent_id = 0;

        // Если у текущей категории есть родители, то обновить или создать родительские категории.
        if (!empty($cat_nesting)) {
            $cat_parent_id = $this->prepare_parent_categories($cat_nesting);
        }

        // Конструкция готового объекта категории.
        $category                = new stdClass();
        $category->id            = $line[$this->map['id']];
        $category->meta_title    = $line[$this->map['name']];
        $category->content_title = $line[$this->map['name']];
        $category->name          = $cat_name;
        $category->url           = $this->translit($category->name);
        $category->parent_id     = $cat_parent_id;

        return $category;
    }

    /**
     * Создать тэги категории из строки csv файла.
     *
     * @param array $line тело csv файла
     *
     * @return array
     */
    public function createTagsFromLine($line)
    {
        return $this->parse_tags($line[$this->map['tags']]);
    }

    /**
     * Извлечь теги в массив.
     *
     * @param string $tags в формате #tag1 #tag2 ... #tag5
     *
     * @return array
     */
    private function parse_tags($tags)
    {
        $result = explode('#', $tags);

        // Удалить лишние пробелы из тэгов и пустые элементы массива.
        foreach ($result as $key => &$tag) {
            if (empty($tag)) {
                unset($result[$key]);
            } else {
                $tag = trim($tag);
            }

        }

        return $result;
    }

    /**
     * Перевести текст из кириллицы в латиницу.
     *
     * @param string $text
     *
     * @return mixed
     */
    private function translit($text)
    {
        $ru = explode('-', "А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я");
        $en = explode('-', "A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch---Y-y---E-e-YU-yu-YA-ya");

        $res = str_replace($ru, $en, $text);
        $res = str_replace("*", "", $res);
        $res = str_replace(".", "", $res);
        $res = str_replace(",", "", $res);
        $res = str_replace("(", "", $res);
        $res = str_replace(")", "", $res);
        $res = str_replace("+", "", $res);
        $res = str_replace("/", "", $res);
        $res = str_replace("-", "_", $res);
        $res = str_replace('"', "", $res);
        $res = str_replace("'", "", $res);
        $res = str_replace("%", "", $res);
        $res = str_replace("№", "", $res);
        $res = str_replace(":", "", $res);
        $res = str_replace("«", "", $res);
        $res = str_replace("»", "", $res);
        $res = preg_replace("/[\s]+/ui", '-', $res);
        $res = strtolower($res);

        return $res;
    }

    /**
     * Создать несуществующие родительские категории.
     *
     * @param array $parent_categories
     *
     * @return int ближайший родитель категории.
     */
    private function prepare_parent_categories($parent_categories)
    {
        $last_parent_cat_id = 0; // id последнего родителя.

        foreach ($parent_categories as $category) {
            $parent_cat_id = $this->simpla->categories->get_category_id_by_name($category);

            // Если категории с таким именем не существует, то создать новую категорию.
            if (!$parent_cat_id) {
                $new_parent_category                = new stdClass();
                $new_parent_category->name          = $category;
                $new_parent_category->meta_title    = $category;
                $new_parent_category->content_title = $category;
                $new_parent_category->url           = $this->translit($category);
                $new_parent_category->parent_id     = $last_parent_cat_id;

                $last_parent_cat_id = $this->simpla->categories->add_category($new_parent_category);
            } else {
                $last_parent_cat_id = $parent_cat_id;
            }
        }

        return $last_parent_cat_id;
    }

    /**
     * Перезаписать теги категории. Сначала удаляет все теги категории, а потом добавляет новые.
     *
     * @param int   $category_id
     *
     * @param array $tags
     *
     * @return void
     */
    public function rewrite_tags($category_id, $tags)
    {
        // Старые теги требуются для удаления оных.
        $old_tags = $this->simpla->products->get_tags(array('object_id' => $category_id, 'type' => 'categori'));

        // Очистить старые теги категории перед добавлнием новых.
        foreach ($old_tags as $old_tag) {
            $this->simpla->products->delete_tag($old_tag->value);
        }

        // Добавить теги к категории.
        $this->add_tags($category_id, $tags);
    }

    /**
     * Добавить тэги к категории.
     *
     * @param int   $category_id
     * @param array $tags
     */
    private function add_tags($category_id, $tags)
    {
        foreach ($tags as $value) {
            $this->simpla->db->query("INSERT IGNORE INTO __tags 
                                      SET type=?, object_id=?, value=?", 'categori', intval($category_id), $value);
            $this->simpla->tags->add_tag(array('name' => $value));
        }
    }
}