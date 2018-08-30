<?php
/**
 * Created by Eugene.
 * User: eugene
 * Date: 29/08/18
 * Time: 10:02
 */

include_once 'ImportBase.php';

class ImportProducts extends ImportBase
{
    /**
     * Имя файла импорта товаров.
     *
     * @var string IMPORT_FILE_PATH
     */
    const IMPORT_FILE_PATH = 'price/export.csv';

    /**
     * Разделитель CSV файла.
     *
     * @var string CSV_DELIMITER
     */
    const CSV_DELIMITER = ',';

    /**
     * ImportProducts constructor.
     */
    public function __construct()
    {
        parent::__construct(self::IMPORT_FILE_PATH, self::CSV_DELIMITER);
    }

    /**
     * Выполнить импорт.
     *
     * @return void
     */
    public function process()
    {
        // TODO: это функционал из go.php. Необходимо переделать.

        $fname = $this->simpla->config->root_dir . self::IMPORT_FILE_PATH;

        // Узнаем какая кодировка у файла
        setlocale(LC_ALL, 'ru_RU.' . $this->get_file_charset($fname));

        $handle = fopen($fname, "r");
        if (!$handle) {
            echo 'Не могу загрузить файл. Проверьте настройки сервера';
        } else {
            // Порядок колонок
            $cols_order = "ctg, ctg_id, name, sku, arkl, prc, kolvo, razmer, cvet, grup, allsv, izbr, zakaz, brand, max_sale, related, old_price, shop_sclad, shop_makarova, shop_204, shop_mira, shop_yog, shop_passaj, short_name, bodyp,tags";
            $temp       = preg_split('/,/', $cols_order);
            $i          = 0;
            foreach ($temp as $tmp) {
                $columns[trim($tmp)] = $i;
                $i++;
            }

            $cols      = true;
            $delimiter = ";";
            # Идем по всем строкам

            while ($cols) {
                $cols = fgetcsv($handle, 0, $delimiter);

                foreach ($columns as $name => $index) {
                    if (isset($cols[$index])) {
                        $values[$name] = $cols[$index];
                    } else {
                        $values[$name] = '';
                    }
                }

                if ($values['name']) {
                    $this->process_product($values);
                }
            }

            fclose($handle);

            foreach ($GLOBALS["related_buffer"] as $related_item) {
                $product_id  = $related_item['product_id'];
                $related_all = $related_item['relative_product'];
                foreach ($related_all as $related_sku) {
                    $related_sku = trim($related_sku);

                    $this->simpla->db->query("SELECT product_id FROM __variants WHERE sku=?", $related_sku);
                    $related_id = $this->simpla->db->result('product_id');
                    if ($related_id) {

                        $this->simpla->db->query("SELECT product_id, related_id FROM __related_products WHERE product_id=? AND related_id=? ", $product_id, $related_id);
                        $result = $this->simpla->db->results();

                        if (!$result) {
                            $this->simpla->db->query('INSERT INTO __related_products (product_id, related_id) VALUES (?,?)', $product_id, $related_id);
                            echo "<span style='color: green; font-weight: bold;'>К товару $product_id добавлен товар код $related_sku </span><br/>";
                        }
                    }
                }

                //include "23sale.php";

                //Комментарии
                $this->simpla->db->query("SELECT * FROM __comments");
                $result = $this->simpla->db->results();
                if (!$result) {
                    $this->process_add_comments();
                } else {
                    $this->process_update_comments();
                }
            }

            $this->process_translit();
        }
    }

    function process_product($params)
    {
        if ($params['ctg'] != "Категория" && $params['name'] != "Наименование"
            && !empty($params['name']) && $params['name'] != '') {

            if (isset($params['ctg'])) {
                $category = trim($params['ctg']);
            } else {
                $category = '';
            }

            if (isset($params['ctg_id'])) {
                $category_id = trim($params['ctg_id']);
            } else {
                $category_id = '';
            }

            if (isset($params['name'])) {
                $model = trim($params['name']);
            } else {
                $model = '';
            }
            if (isset($params['opt'])) {
                $opt = trim($params['opt']);
            } else {
                $opt = '';
            }
            if (isset($params['sku'])) {
                $sku = trim($params['sku']);
            } else {
                $sku = '';
            }
            if ($params['prc'] != '') {
                $price = str_replace(',', '.', $params['prc']);
            } else {
                $price = 0;
            }
            if (isset($params['qty'])) {
                $quantity = intval($params['qty']);
            } else {
                $quantity = '';
            }
            if (isset($params['ann'])) {
                $description = trim($params['ann']);
            } else {
                $description = '';
            }
            if (isset($params['dsc'])) {
                $body = trim($params['dsc']);
            } else {
                $body = '';
            }
            if (isset($params['url'])) {
                $url = trim($params['url']);
            } else {
                $url = '';
            }
            if (isset($params['mttl'])) {
                $meta_title = trim($params['mttl']);
            } else {
                $meta_title = '';
            }
            if (isset($params['mkwd'])) {
                $meta_keywords = trim($params['mkwd']);
            } else {
                $meta_keywords = '';
            }
            if (isset($params['mdsc'])) {
                $meta_description = trim($params['mdsc']);
            } else {
                $meta_description = '';
            }
            if (isset($params['enbld'])) {
                $enabled = trim($params['enbld']);
            } else {
                $enabled = '';
            }
            if (isset($params['hit'])) {
                $hit = trim($params['hit']);
            } else {
                $hit = '';
            }
            if (isset($params['simg'])) {
                $small_image = trim($params['simg']);
            } else {
                $small_image = '';
            }
            if (isset($params['limg'])) {
                $large_image = trim($params['limg']);
            } else {
                $large_image = '';
            }
            if (isset($params['imgs'])) {
                $images_string = trim($params['imgs']);
            } else {
                $images_string = '';
            }
            if (isset($params['kolvo'])) {
                $kolvo = trim($params['kolvo']);
            } else {
                $kolvo = 0;
            }

            if (isset($params['razmer'])) {
                $razmer = trim($params['razmer']);
            } else {
                $razmer = "";
            }
            if (isset($params['cvet'])) {
                $cvet = trim($params['cvet']);
            } else {
                $cvet = "";
            }
            if (isset($params['grup'])) {
                $grup = trim($params['grup']);
            } else {
                $grup = "";
            }
            if (isset($params['allsv'])) {
                $allsv = trim($params['allsv']);
            } else {
                $allsv = "";
            }
            if (isset($params['izbr'])) {
                $izbr = trim($params['izbr']);
            } else {
                $izbr = "";
            }
            if (isset($params['bodyp'])) {
                $bodyp = trim($params['bodyp']);
            } else {
                $bodyp = "";
            }
            if (isset($params['short_name'])) {
                $short_name = trim($params['short_name']);
            } else {
                $short_name = "";
            }
            if (isset($params['max_sale'])) {
                $max_sale = trim($params['max_sale']);
            } else {
                $max_sale = "";
            }
            if (isset($params['related'])) {
                $related = trim($params['related']);
            } else {
                $related = "";
            }
            if (isset($params['old_price'])) {
                $old_price = trim($params['old_price']);
            } else {
                $old_price = "";
            }
            if (isset($params['shop_sclad'])) {
                $shop_sclad = trim($params['shop_sclad']);
            } else {
                $shop_sclad = "";
            }
            if (isset($params['shop_makarova'])) {
                $shop_makarova = trim($params['shop_makarova']);
            } else {
                $shop_makarova = "";
            }
            if (isset($params['shop_204'])) {
                $shop_204 = trim($params['shop_204']);
            } else {
                $shop_204 = "";
            }
            if (isset($params['shop_mira'])) {
                $shop_mira = trim($params['shop_mira']);
            } else {
                $shop_mira = "";
            }
            if (isset($params['shop_yog'])) {
                $shop_yog = trim($params['shop_yog']);
            } else {
                $shop_yog = "";
            }
            if (isset($params['shop_passaj'])) {
                $shop_passaj = trim($params['shop_passaj']);
            } else {
                $shop_passaj = "";
            }

            if ($params['zakaz'] != '') {
                $zakaz = trim($params['zakaz']);
            } else {
                $zakaz = 0;
            }

            $url = $this->translit($model);

            if (isset($params['tags'])) {
                $tags = explode("#", $params['tags']);
            } else {
                $tags = array();
            }


            $brand = 0;
            if (isset($params['brand'])) {
                $brand = $this->process_brand(trim($params['brand']));
            }

            // Парсинг характеристик
            if (!empty($allsv)) {
                $haracts      = explode("|", $allsv);
                $haractsIds   = array();
                $haractsNames = array();
                foreach ($haracts as $key => $value) {
                    $valueArray     = explode("@", $value);
                    $haractsIds[]   = $this->process_haract($valueArray[0]);
                    $haractsNames[] = $valueArray[0];
                }
                $haractsRep = str_replace($haractsNames, $haractsIds, $haracts);
                for ($i = 0; $i < count($haractsRep); $i++) {
                    $haractsRep[$i] = explode('@', $haractsRep[$i]);
                }
            }

            $enabled = 1;

            /* ищем по ид, если есть заменяем цену, кол-во цвет и размер */
            $this->simpla->db->query('SELECT id FROM __variants WHERE external_id=?', $sku);
            $product_id = $this->simpla->db->result('id');

            $this->simpla->db->query('SELECT product_id FROM __variants WHERE external_id=?', $sku);
            $real_product_id = $this->simpla->db->result('product_id');

            $meta_title       = $model . " - " . $category;
            $meta_keywords    = $category . " " . $model;
            $meta_description = $category . " " . $model;

            $productId = $real_product_id;
            if ($productId > 0) {
                foreach ($tags as $value) {

                    if ($value == '') {
                        continue;
                    }

                    $query = $this->simpla->db->placehold("INSERT 
                                                           IGNORE INTO __tags
                                                           SET type=?, object_id=?, value=?", 'product',
                        intval($productId), $value);
                    $this->simpla->db->query($query);

                    if (!$this->simpla->tags->get_tag((string)$value, 'sdsd')) {
                        $tag       = new stdClass;
                        $tag->name = $value;

                        $this->simpla->tags->add_tag($tag);

                    }
                }

                $this->simpla->db->query('SELECT category_id FROM __products_categories WHERE product_id=? LIMIT 1', $productId);
                $categoryIdFromBase = $this->simpla->db->result('category_id');
                if (!empty($categoryIdFromBase) && $categoryIdFromBase != "" && $category_id != $categoryIdFromBase) {
                    $this->simpla->db->query('UPDATE __products_categories SET category_id=? WHERE product_id=? LIMIT 1', $category_id, $productId);
                    $this->update_cat($category_id, $productId);
                }
                if ($categoryIdFromBase == "" || empty($categoryIdFromBase)) {
                    $this->simpla->db->query('INSERT INTO __products_categories (category_id,product_id,position) VALUES (?,?,?) ', $category_id, $productId, 0);
                }
            }
            if ($product_id > 0) {
                if ($cvet != "" and $razmer != "") {
                    $variant_n = $cvet . " / " . $razmer;
                } else {
                    $variant_n = $cvet . $razmer;
                }

                echo "<div style='background-color: #fff;padding:0 10px;width: 30px'> " . $real_product_id . "</div>";
                echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 100px'> " . $params['sku'] . "</div>";
                echo "<div style='background-color: #fff;padding:0 10px;width: 350px'> " . $params['ctg'] . "</div>";
                echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 350px'> " . $params['name'] . "</div>";
                echo "<div style='background-color: #fff;padding:0 10px;width: 94px'> " . $params['razmer'] . "</div>";
                echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 50px'> " . $params['prc'] . "</div>";
                echo "<div style='background-color: #fff;padding:0 10px;width: 50px'> " . $params['old_price'] . "</div>";
                echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 35px'> " . $params['kolvo'] . "</div>";
                echo "<div style='background-color: #fff;padding:0 10px;width: 35px'> " . $params['zakaz'] . "</div>";
                echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 35px'> " . $params['max_sale'] . "</div>";
                echo "<div style='background-color: #fff;padding:0 10px;width: 105px'>обновили</div>";
                echo "</div>";

                if ($izbr != "") {
                    echo "<div style='background-color: #fff;padding:0 10px;'>";
                    $this->add_images($real_product_id, $izbr);
                    echo "</div>";
                }
                if (!empty($related)) {
                    $related_all = explode(",", $related);

                    $related_arr = array();
                    foreach ($related_all as $related_item) {
                        $related_item  = trim($related_item);
                        $related_arr[] = $related_item;
                    }

                    $GLOBALS["related_buffer"][] = array(
                        "product_id"       => $real_product_id,
                        "relative_product" => $related_arr,
                    );
                }

                $nameForProduct = $model;
                if ($grup != "") {
                    $nameForProduct = $grup;
                }

                $this->simpla->variants->log('UPDATE __variants SET name=?, price=?, compare_price=?, stock=?,name=?,pod_zakaz=?, max_sale=?, shop_sclad=?, shop_makarova=?, shop_204=?, shop_mira=?, shop_yog=?, shop_passaj=? WHERE external_id=? LIMIT 1' . " $model, $price, $old_price, $kolvo, $variant_n, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj, $sku");
                $this->simpla->db->query('UPDATE __variants SET name=?, price=?, compare_price=?, stock=?,name=?,pod_zakaz=?, max_sale=?, shop_sclad=?, shop_makarova=?, shop_204=?, shop_mira=?, shop_yog=?, shop_passaj=? WHERE external_id=? LIMIT 1', $model, $price, $old_price, $kolvo, $variant_n, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj, $sku);
                $this->simpla->db->query('UPDATE __products SET name=?, body=?, annotation=?, brand_id=?,pod_zakaz=?, max_sale=? WHERE id=? LIMIT 1', $model, $bodyp, $short_name, $brand, $zakaz, $max_sale, $real_product_id);

                if (!empty($allsv)) {
                    $this->simpla->db->query('DELETE FROM __options WHERE product_id=?', $productId);

                    foreach ($haractsRep as $key => $value) {
                        if (!empty($value[0]) && !empty($value[1])) {
                            $this->simpla->db->query('INSERT INTO __options (product_id,feature_id,value) VALUES (?,?,?)', $productId, $value[0], $value[1]);
                        }
                    }
                }
            } elseif ($model != "") {
                // возможно такой товар есть, а это просто вариант.
                if ($grup != "") {
                    $this->simpla->db->query('SELECT product_id FROM __variants WHERE gr=?', $grup);
                    $product_id = $this->simpla->db->result('product_id');
                }

                if ($product_id > 0) {
                    if ($cvet != "" and $razmer != "") {
                        $variant_n = $cvet . " / " . $razmer;
                    } else {
                        $variant_n = $cvet . $razmer;
                    }
                    $nameForProduct = $model;
                    if ($grup != "") {
                        $nameForProduct = $grup;
                    }
                    //Обновление статуса под заказ и изображения
                    $this->simpla->db->query('UPDATE __products SET name=?,body=?,annotation=?,brand_id=?,pod_zakaz=?, max_sale=? WHERE id=? LIMIT 1', $nameForProduct, $bodyp, $short_name, $brand, $zakaz, $max_sale, $product_id);
                    $query = $this->simpla->db->placehold("INSERT INTO __variants(name, product_id, sku, price, compare_price, stock, gr, external_id, pod_zakaz, max_sale, shop_sclad, shop_makarova, shop_204, shop_mira, shop_yog, shop_passaj) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $variant_n, $product_id, $sku, $price, $old_price, $kolvo, $grup, $sku, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj);
                    $this->simpla->variants->log($query);
                    $this->simpla->db->query($query);

                    $query = $this->simpla->db->placehold("INSERT INTO __products_categories(product_id,category_id,position) VALUES(?,?,0)", $product_id, $category_id);
                    $this->simpla->db->query($query);


                    $this->add_cat($category_id, $product_id);


                    echo "<div style='background-color: #fff;padding:0 10px;width: 30px'> " . $product_id . "</div>";
                    echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 100px'> " . $params['sku'] . "</div>";
                    echo "<div style='background-color: #fff;padding:0 10px;width: 350px'> " . $params['ctg'] . "</div>";
                    echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 350px'> " . $params['name'] . "</div>";
                    echo "<div style='background-color: #fff;padding:0 10px;width: 94px'> " . $params['razmer'] . "</div>";
                    echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 50px'> " . $params['prc'] . "</div>";
                    echo "<div style='background-color: #fff;padding:0 10px;width: 50px'> " . $params['old_price'] . "</div>";
                    echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 35px'> " . $params['kolvo'] . "</div>";
                    echo "<div style='background-color: #fff;padding:0 10px;width: 35px'> " . $params['zakaz'] . "</div>";
                    echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 35px'> " . $params['max_sale'] . "</div>";
                    echo "<div style='background-color: #fff;padding:0 10px;width: 105px'>добавили вариант</div>";
                    echo "</div>";
                    if (!empty($allsv)) {
                        $this->simpla->db->query('DELETE FROM __options WHERE product_id=?', $productId);

                        foreach ($haractsRep as $key => $value) {
                            if (!empty($value[0]) && !empty($value[1])) {
                                $this->simpla->db->query('INSERT INTO __options (product_id,feature_id,value) VALUES (?,?,?)', $productId, $value[0], $value[1]);
                            }
                        }
                    }
                } else {

                    $nameForProduct = $model;
                    if ($grup != "") {
                        $nameForProduct = $grup;
                    }
                    $query = $this->simpla->db->query("INSERT INTO __products(name, url, body, brand_id, content_title, meta_title, meta_keywords, meta_description, visible, external_id, pod_zakaz, max_sale) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)", $nameForProduct, $url, $bodyp, $short_name, $brand, $nameForProduct, $nameForProduct, $meta_keywords, $meta_description, 1, $sku, $zakaz, $max_sale);


                    $pro_id = $this->simpla->db->insert_id();

                    if ($pro_id > 0) {


                        foreach ($tags as $value) {

                            if ($value == '') {
                                continue;
                            }

                            $query = $this->simpla->db->placehold("INSERT IGNORE INTO __tags SET type=?, object_id=?, value=?", 'product', intval($pro_id), $value);
                            $this->simpla->db->query($query);

                            if (!$this->simpla->tags->get_tag((string)$value, 'sdsd')) {
                                $tag       = new stdClass;
                                $tag->name = $value;

                                $this->simpla->tags->add_tag($tag);

                            }
                        }


                        echo "<div style='background-color: #fff;padding:0 10px;width: 30px'> " . $pro_id . "</div>";
                        echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 100px'> " . $params['sku'] . "</div>";
                        echo "<div style='background-color: #fff;padding:0 10px;width: 350px'> " . $params['ctg'] . "</div>";
                        echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 350px'> " . $params['name'] . "</div>";
                        echo "<div style='background-color: #fff;padding:0 10px;width: 94px'> " . $params['razmer'] . "</div>";
                        echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 50px'> " . $params['prc'] . "</div>";
                        echo "<div style='background-color: #fff;padding:0 10px;width: 50px'> " . $params['old_price'] . "</div>";
                        echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 35px'> " . $params['kolvo'] . "</div>";
                        echo "<div style='background-color: #fff;padding:0 10px;width: 35px'> " . $params['zakaz'] . "</div>";
                        echo "<div style='background-color: #c0c0c0;padding:0 10px;width: 35px'> " . $params['max_sale'] . "</div>";
                        echo "<div style='background-color: #fff;padding:0 10px;width: 105px'>добавили товар</div>";
                        if ($izbr != "") {
                            echo "<div style='background-color: #fff;padding:0 10px;'>";
                            $this->add_images($pro_id, $izbr);
                            echo "</div>";
                        }
                        if (!empty($related)) {
                            $related_all = explode(",", $related);

                            $related_arr = array();
                            foreach ($related_all as $related_item) {
                                $related_item  = trim($related_item);
                                $related_arr[] = $related_item;
                            }

                            $GLOBALS["related_buffer"][] = array(
                                "product_id"       => $pro_id,
                                "relative_product" => $related_arr,
                            );
                        }
                        echo "</div>";

                        if ($cvet != "" and $razmer != "") {
                            $variant_n = $cvet . " / " . $razmer;
                        } else {
                            $variant_n = $cvet . $razmer;
                        }

                        $query = $this->simpla->db->placehold("INSERT INTO __variants(name, product_id, sku, price, compare_price, stock, gr, external_id, pod_zakaz, max_sale, shop_sclad, shop_makarova, shop_204, shop_mira, shop_yog, shop_passaj) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $variant_n, $pro_id, $sku, $price, $old_price, $kolvo, $grup, $sku, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj);
                        $this->simpla->db->query($query);
                        $this->simpla->variants->log($query);

                        $query = $this->simpla->db->placehold("INSERT INTO __products_categories(product_id,category_id,position) VALUES(?,?,0)", $pro_id, $category_id);
                        $this->simpla->db->query($query);

                        $this->add_cat($category_id, $pro_id);


                        if (!empty($allsv)) {
                            foreach ($haractsRep as $key => $value) {
                                if (!empty($value[0]) && !empty($value[1])) {
                                    $this->simpla->db->query('INSERT INTO __options (product_id,feature_id,value) VALUES (?,?,?)', $pro_id, $value[0], $value[1]);

                                    $this->simpla->db->query('INSERT INTO __categories_features (category_id,feature_id) VALUES (?,?)', $category_id, $value[0]);
                                }
                            }
                        }

                    }
                }
            }

        }
    }


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

    private function process_brand($name)
    {
        $name = trim($name);
        if (!empty($name)) {
            if ($name == 1) {
                $name     = "Ставрополь";
                $brand_id = 3;
            } else {
                if ($name == 2) {
                    $name     = "Михайловск";
                    $brand_id = 5;
                } else {
                    if ($name == 3) {
                        $name     = "Пятигорск";
                        $brand_id = 7;
                    }
                }
            }

            $query = $this->simpla->db->placehold("SELECT * FROM __brands WHERE name=? LIMIT 1", $name);
            $this->simpla->db->query($query);
            $exist_brand = $this->simpla->db->result();
            $brand_id    = $exist_brand->id;
            if (!empty($brand_id)) {
                return $brand_id;
            }

            $query = $this->simpla->db->placehold("INSERT INTO __brands(name,url) VALUES(?,?)", $name, $name);
            $this->simpla->db->query($query);
            $brand_id = $this->simpla->db->insert_id();
            return $brand_id;
        }
        return 0;
    }

    private function process_haract($haractName)
    {
        $haractName = trim($haractName);
        if (!empty($haractName)) {

            $query = $this->simpla->db->placehold("SELECT id FROM __features WHERE name=? LIMIT 1", $haractName);
            $this->simpla->db->query($query);
            $exist_feature = $this->simpla->db->result("id");
            if (!empty($exist_feature)) {
                return $exist_feature;
            }

            $query = $this->simpla->db->placehold("INSERT INTO __features(name) VALUES(?)", $haractName);
            $this->simpla->db->query($query);

            $id = $this->simpla->db->insert_id();

            $query = $this->simpla->db->placehold("UPDATE __features SET position=id WHERE id=? LIMIT 1", $id);

            $this->simpla->db->query($query);

            return $id;
        }
        return 0;
    }

    private function add_images($product_id, $izbr)
    {
        $izbr_all = explode(",", $izbr);

        $this->simpla->db->query("SELECT id, product_id FROM __variants WHERE product_id=?", $product_id);
        $result_2 = $this->simpla->db->results();

        $image_exist = 0;
        foreach ($result_2 as $variant) {
            $this->simpla->db->query("SELECT * FROM __images WHERE product_id=?", $variant->product_id);
            $result3 = $this->simpla->db->result();
            if ($result3) {
                $image_exist += 1;
            }
        }

        foreach ($izbr_all as $izbr_item) {
            $old_izbr_item = trim($izbr_item);
            $izbr_item     = $this->translit_img(trim($izbr_item));

            $fileimg = $izbr_item;
            if (preg_match('/(.+)\.([^\.]+)$/', $fileimg, $matches)) {
                $path   = $this->simpla->config->root_dir . "images1c/" . $old_izbr_item;
                $path_2 = $this->simpla->config->root_dir . "files/originals/" . $izbr_item;

                $resize_dir = $this->simpla->config->root_dir . "files/products/";

                $path_parts   = pathinfo($path);
                $path_parts_2 = pathinfo($path_2);


                if ((filesize($path) != filesize($path_2))) {
                    $resize_files = scandir($resize_dir);
                    foreach ($resize_files as $resize_file_name) {
                        if (substr_count($resize_file_name, $path_parts_2['filename'])) {
                            unlink("$resize_dir/$resize_file_name");
                            echo "<span style='color: orange; font-weight: bold;'>Изображение $resize_file_name удалено из products </span><br/>";
                        }
                    }
                }

                $this->simpla->db->query("SELECT * FROM __images WHERE product_id=? AND filename=?", $product_id, $izbr_item);
                $result = $this->simpla->db->result();

                if ((!$result && $image_exist == 0)) {

                    if (copy($path, $path_2)) {
                        if (file_exists($path_2)) {
                            $query = $this->simpla->db->query("INSERT INTO __images(product_id, filename) VALUES(?,?)", $product_id, $izbr_item);

                            echo "<span style='color: green; font-weight: bold;'>Изображение '" . $path_parts['basename'] . " " . filesize($path) . " " . "' добавлено как '" . $path_parts_2['basename'] . " " . filesize($path) . "'</span><br/>";
                        }
                    } else {
                        echo "<span style='color: red; font-weight: bold;'>Изображение '" . $path_parts['basename'] . "' не добавлено</span><br/>";
                    }
                }
            }
        }
    }

    private function translit_img($text)
    {
        $ru = explode('-', "А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я");
        $en = explode('-', "A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch---Y-y---E-e-YU-yu-YA-ya");

        $res = str_replace($ru, $en, $text);
        $res = str_replace(" ", "_", $res);
        $res = str_replace("*", "_", $res);
        $res = str_replace(",", "_", $res);
        $res = str_replace("(", "_", $res);
        $res = str_replace(")", "_", $res);
        $res = str_replace("+", "_", $res);
        $res = str_replace("/", "_", $res);
        $res = str_replace("-", "_", $res);
        $res = str_replace('"', "_", $res);
        $res = str_replace("'", "_", $res);
        $res = str_replace("%", "_", $res);
        $res = str_replace("№", "_", $res);
        $res = str_replace(":", "_", $res);
        $res = str_replace("«", "_", $res);
        $res = str_replace("»", "_", $res);
        return $res;
    }

    private function add_cat($category_id, $productId)
    {
        $nameCat = $this->getCatNameId($category_id);
        echo "<hr />TestCatADD<hr /><hr />$nameCat<hr /><hr /><hr />";

        if ($nameCat !== '') {
            $this->simpla->db->query('SELECT id FROM __categories WHERE name=? ', $nameCat);
            $results = $this->simpla->db->results();
            foreach ($results as $val) {
                $this->simpla->db->query('INSERT IGNORE INTO __products_categories (category_id,product_id,position) VALUES (?,?,?) ', $val->id, $productId, 0);

                echo "<hr /><hr /><hr /><hr />" . $val->id . "<hr />";
            }
        }

    }

    private function update_cat($category_id, $productId)
    {
        $nameCat = $this->getCatNameId($category_id);

        echo "<hr />TestCat<hr /><hr />$nameCat<hr /><hr /><hr />";

        if ($nameCat !== '') {
            $this->simpla->db->query('SELECT id FROM __categories WHERE name=? ', $nameCat);
            $results = $this->simpla->db->results();
            foreach ($results as $val) {
                $this->simpla->db->query('UPDATE __products_categories SET category_id=? WHERE product_id=? LIMIT 1', $val->id, $productId);

                echo "<hr /><hr /><hr /><hr />" . $val->id . "<hr />";
            }
        }

    }

    private function getCatNameId($id)
    {
        $this->simpla->db->query("SELECT name FROM __categories WHERE id=?  LIMIT 1", $id);
        $cat = $this->simpla->db->result("name");
        return $cat;

    }

    private function process_add_comments()
    {
        $file = $this->simpla->config->root_dir . 'price/comments.txt';

        $data = unserialize(file_get_contents($file));

        foreach ($data as $comment) {
            if ($comment->type == "product") {
                $this->simpla->db->query("SELECT id FROM __products WHERE external_id = ?", $comment->object_sku);
                $result_id = $this->simpla->db->result("id");
                if (!$result_id) {
                    $product_id = 0;
                } else {
                    $product_id = $result_id;
                }
            } else {
                $product_id = $comment->object_id;
            }

            $this->simpla->db->query('INSERT INTO __comments (date, ip, object_id, object_sku, name, email, text, rating, type, approved) VALUES (?,?,?,?,?,?,?,?,?,?)', $comment->date, $comment->ip, $product_id, $comment->object_sku, $comment->name, $comment->email, $comment->text, $comment->rating, $comment->type, $comment->approved);
        }
    }

    private function process_update_comments()
    {
        $this->simpla = new Simpla();

        $this->simpla->db->query("SELECT id, object_sku FROM __comments WHERE object_id = '0'");
        $result = $this->simpla->db->results();

        foreach ($result as $item) {
            $this->simpla->db->query("SELECT id FROM __products WHERE external_id = ?", $item->object_sku);
            $product_id = $this->simpla->db->result("id");

            if ($product_id) {
                $this->simpla->db->query('UPDATE __comments SET object_id = ? WHERE id = ? LIMIT 1', $product_id, $item->id);
            }
        }
    }

    private function process_translit()
    {

        /**
         * Проставляем урлы для свойств
         */
        $this->simpla->db->query("SELECT id, name FROM __features ORDER BY id");
        foreach ($this->simpla->db->results() as $f) {
            $this->simpla->features->update_feature($f->id, array('url' => $this->simpla->features->translit($f->name)));
        }

        /**
         * Транслитерируем значения свойств
         */
        $this->simpla->db->query("SELECT * FROM __options");
        foreach ($this->simpla->db->results() as $o) {
            $this->simpla->features->update_option($o->product_id, $o->feature_id, $o->value);
        }
    }

}