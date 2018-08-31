<?PHP




error_reporting(E_ALL ^ E_NOTICE);

chdir('../');
include('api/Simpla.php');

include('Smarty/libs/Smarty.class.php');

setlocale(LC_ALL, "ru_RU.UTF-8");
echo "start";

############################################
# Class Import
############################################

$csv_line_maxlength = 20000;
$allowed_extensions = array('csv', 'txt');
$subcategory_delimiter = '/';

$products_added = 0;
$products_updated = 0;
$variants_added = 0;
$variants_updated = 0;

$related_buffer = array();

// $server_path = "/var/www/html/lidsport/";
$server_path = "/var/www/admin/data/www/adv26.ru/";

fetch();

function fetch() {
    $sim = new Simpla();
    /* обнулим товары */
    $sim->db->query('UPDATE __variants SET stock=?', 0);
    $sim->db->query('TRUNCATE s_images');

    $sim->variants->log('UPDATE __variants SET stock=?');

    $title = $lang->PRODUCTS_IMPORT;

    /* if(isset($_POST['format']) && !empty($_POST['format']) && !empty($_FILES['file']['tmp_name']))
      { */


    $format = "txt";
    $fname = $GLOBALS["server_path"] . "price/export.csv";


    /* if(!in_array(end(explode(".", $_FILES['file']['name'])), $allowed_extensions))
      {
      $error_msg = 'Неподдерживаемый тип файла';
      }
      else
      { */
    // Узнаем какая кодировка у файла
    $fh = fopen($fname, 'r');
    $teststring = fread($fh, 2);
    fclose($fh);

    // Кодировки
    if (preg_match('//u', $teststring)) {
        $charset = 'UTF8';
    } else {
        $charset = 'CP1251';
    }
    // $charset = 'CP1251';
    echo $charset;
    setlocale(LC_ALL, 'ru_RU.' . $charset);


    $handle = fopen($fname, "r");
    if (!$handle) {
        echo 'Не могу загрузить файл. Проверьте настройки сервера';
    } else {
        // Максимальное время выполнения скрипта
        $max_time = @ini_get('max_execution_time');
        if (!$max_time)
            $max_time = 30;

        // Порядок колонок
        $cols_order = "ctg, name, sku, dsc, prc, kolvo, razmer, cvet, grup, allsv, izbr, zakaz, brand, max_sale, related, old_price, shop_sclad, shop_makarova, shop_204, shop_mira, shop_yog, shop_passaj, short_name, bodyp,tags";
        $temp = preg_split('/,/', $cols_order);
        $i = 0;
        foreach ($temp as $tmp) {
            $columns[trim($tmp)] = $i;
            $i++;
        }


        $start_time = microtime(true);
        $time_elapsed = 0;
        $cols = true;
        $delimiter = ";";
        $str_kol = 0;
        # Идем по всем строкам

        $text = '111211';

        $fp = fopen("roooo.txt", "a");
        fwrite($fp, $text);
        fclose($fp);

        while ($cols) {
            $cols = fgetcsv($handle, $csv_line_maxlength, $delimiter);

            foreach ($columns as $name => $index) {
//						echo "$cols[$index] <span style='background:red'>@</span> ";
                if (isset($cols[$index]))
                    $values[$name] = $cols[$index];
                else
                    $values[$name] = '';
            }
            if ($values['name']) {

                process_product($values);
            }
            /* ?><pre><?print_r($values)?></pre><? */
            $current_time = microtime(true);
            $time_elapsed = $current_time - $start_time;
        }
        fclose($handle);

        foreach ($GLOBALS["related_buffer"] as $related_item) {
            $product_id = $related_item['product_id'];
            $related_all = $related_item['relative_product'];
            foreach ($related_all as $related_sku) {
                $related_sku = trim($related_sku);

                $sim->db->query("SELECT product_id FROM __variants WHERE sku=?", $related_sku);
                $related_id = $sim->db->result(product_id);
                if ($related_id) {

                    $sim->db->query("SELECT product_id, related_id FROM __related_products WHERE product_id=? AND related_id=? ", $product_id, $related_id);
                    $result = $sim->db->results();

                    if (!$result) {
                        $sim->db->query('INSERT INTO __related_products (product_id, related_id) VALUES (?,?)', $product_id, $related_id);
                        echo "<span style='color: green; font-weight: bold;'>К товару $product_id добавлен товар код $related_sku </span><br/>";
                    }
                }
            }
        }

        include "double_categories.php";
        include "23sale.php";

        //Комментарии
        $sim->db->query("SELECT * FROM __comments");
        $result = $sim->db->results();
        print_r($result);
        if (!$result) {
            process_add_comments();
        } else {
            process_update_comments();
        }
        //Комментарии
    }

    process_translit();

    $sim->money->design->smarty->clearAllCache();
}

//////////////////////
//////////////////////
function process_translit() {
    $simpla = new Simpla();
    /**
     * Проставляем урлы для свойств
     */
    $simpla->db->query("SELECT id, name FROM __features ORDER BY id");
    foreach ($simpla->db->results() as $f) {
        $simpla->features->update_feature($f->id, array('url' => $simpla->features->translit($f->name)));
    }

    /**
     * Транслитерируем значения свойств
     */
    $simpla->db->query("SELECT * FROM __options");
    foreach ($simpla->db->results() as $o) {
        $simpla->features->update_option($o->product_id, $o->feature_id, $o->value);
    }
}


//////////////////////
//////////////////////
function process_category($name) {
    $simpla = new Simpla();
    // echo "<br>-".$name;
    // Поле "категория" может состоять из нескольких имен, разделенных subcategory_delimiter-ом
    // Только неэкранированный subcategory_delimiter может разделять категории

    /* $delimeter = $subcategory_delimiter;
      $regex = "/\\DELIMETER((?:[^\\\\\DELIMETER]|\\\\.)*)/";
      $regex = str_replace('DELIMETER', $delimeter, $regex);
      $names = preg_split($regex, $name, 0, PREG_SPLIT_DELIM_CAPTURE); */

    $names = explode("/", $name);

    $result_category_id = null;
    $current_parent = 0;
    $all = count($names);
    $i = 0;
    for ($ii = 0; $ii < $all; $ii++) {
        $name = trim($names[$ii]);
        if (!empty($name) and $name != "Категория") {
            //echo "<br>---".$name;
            $simpla->db->query("SELECT id FROM __categories WHERE parent_id=? AND name=? LIMIT 1", $current_parent, $name);
            // $simpla->db->query("SELECT id FROM __categories WHERE name=? LIMIT 1", $name);
            $cat = $simpla->db->result("id");


            if ($cat > 0) {
                $result_category_id = $cat;
                $current_parent = $result_category_id;
            } else {
                $url = translit($name);
                $query = $simpla->db->query("INSERT INTO __categories(name, parent_id, content_title, meta_title, url) VALUES(?,?,?,?,?)", $name, $current_parent, $name, $name, $url);
                $result_category_id = $simpla->db->insert_id();
                $current_parent = $result_category_id;
            }

            //echo "<br> $i / $all category = ".$result_category_id;
        }
    }
    //echo $result_category_id;
    return $result_category_id;
}

//////////////////////
//////////////////////
function process_brand($name) {
    $simpla = new Simpla();
    $name = trim($name);
    if (!empty($name)) {
        if ($name == 1) {
            $name = "Ставрополь";
            $brand_id = 3;
        } else if ($name == 2) {
            $name = "Михайловск";
            $brand_id = 5;
        } else if ($name == 3) {
            $name = "Пятигорск";
            $brand_id = 7;
        } else {

        }
        // $name = iconv("UTF-8","WINDOWS-1251",$name);

        $query = $simpla->db->placehold("SELECT * FROM __brands WHERE name=? LIMIT 1", $name);
        $simpla->db->query($query);
        $exist_brand = $simpla->db->result();
        $brand_id = $exist_brand->id;
//			echo ' b2'.$brand_id;
        if (!empty($brand_id))
            return $brand_id;

        $query = $simpla->db->placehold("INSERT INTO __brands(name,url) VALUES(?,?)", $name, $name);
        $simpla->db->query($query);
        $brand_id = $simpla->db->insert_id();
        $brands[$k]->brand_id = $brand_id;
        $brands[$k]->name = $name;
//			echo ' b3'.$brand_id;
        return $brand_id;

//			echo "<br> brand = ".$brand_id."/".$name;
    }
    return 0;
}

function process_haract($haractName) {
    $simpla = new Simpla();
    $haractName = trim($haractName);
    if (!empty($haractName)) {

        $query = $simpla->db->placehold("SELECT id FROM __features WHERE name=? LIMIT 1", $haractName);
        $simpla->db->query($query);
        $exist_feature = $simpla->db->result("id");
        if (!empty($exist_feature))
            return $exist_feature;

        $query = $simpla->db->placehold("INSERT INTO __features(name) VALUES(?)", $haractName);
        $simpla->db->query($query);

        $id = $simpla->db->insert_id();

        $query = $simpla->db->placehold("UPDATE __features SET position=id WHERE id=? LIMIT 1", $id);

        $simpla->db->query($query);

        return $id;
    }
    return 0;
}



function update_cat($category_id,$productId){
    $simpla = new Simpla();


    $nameCat = getCatNameId($category_id);

    echo "<hr />TestCat<hr /><hr />$nameCat<hr /><hr /><hr />";


    if($nameCat !== '')
    {

        $simpla->db->query('SELECT id FROM __categories WHERE name=? ', $nameCat);
        $results = $simpla->db->results();
        foreach($results as $val)
        {
            $simpla->db->query('UPDATE __products_categories SET category_id=? WHERE product_id=? LIMIT 1', $val->id, $productId);

            echo "<hr /><hr /><hr /><hr />".$val->id."<hr />";
        }
    }





}

function getCatNameId($id){

    $simpla = new Simpla();

    $simpla->db->query("SELECT name FROM __categories WHERE id=?  LIMIT 1", $id);
    $cat = $simpla->db->result("name");
    return $cat;

}

function getCatIdName($categoryName){


    $simpla = new Simpla();



    $names = explode("/",$categoryName);


    $result_category_id = null;
    $current_parent = 0;

    foreach($names as $name)
    {
        $name = trim($name);



        if(!empty($name) and $name!="Категория")
        {

            $simpla->db->query("SELECT id FROM __categories WHERE parent_id=? AND name=? LIMIT 1", $current_parent, $name);

            $cat = $simpla->db->result("id");



            if($cat>0)
            {
                $result_category_id = $cat;
                $current_parent = $result_category_id;
            }



        }

    }

    return $result_category_id;

}

function add_cat($category_id,$productId){
    $simpla = new Simpla();




    $nameCat = getCatNameId($category_id);


    echo "<hr />TestCatADD<hr /><hr />$nameCat<hr /><hr /><hr />";


    if($nameCat !== '')
    {

        $simpla->db->query('SELECT id FROM __categories WHERE name=? ', $nameCat);
        $results = $simpla->db->results();
        foreach($results as $val)
        {
            $simpla->db->query('INSERT IGNORE INTO __products_categories (category_id,product_id,position) VALUES (?,?,?) ', $val->id, $productId, 0);

            echo "<hr /><hr /><hr /><hr />".$val->id."<hr />";


        }
    }






}

//////////////////////
//////////////////////
function process_product($params) {

    //echo "<br>путь ".$params['ctg']." товар артикл ".$params['sku']." хз ".$params['dsc']." склад ".$params['sclad']." кол-во ".$params['kolvo']." цена ".$params['prc']." шт ".$params['name'];
    echo "<div style='display: flex;flex-wrap: wrap;justify-content: space-between;border: 1px solid #000'>";

    if ($params['ctg'] != "Категория" and $params['name'] != "Наименование" && !empty($params['name']) && $params['name'] != '') {

        $simpla = new Simpla();

        // if(isset($params['ctg'])) $category = trim(iconv('windows-1251', 'UTF-8', $params['ctg'])); else $category = '';
        if (isset($params['ctg']))
            $category = trim($params['ctg']);
        else
            $category = '';





        //if(isset($params['brand'])) $brand = trim($params['brnd']); else $brand = '';
        // if(isset($params['name'])) $model = trim(iconv('windows-1251', 'UTF-8', $params['name'])); else $model = '';
        if (isset($params['name']))
            $model = trim($params['name']);
        else
            $model = '';
        if (isset($params['opt']))
            $opt = trim($params['opt']);
        else
            $opt = '';
        if (isset($params['sku']))
            $sku = trim($params['sku']);
        else
            $sku = '';
        if ($params['prc'] != '')
            $price = str_replace(',', '.', $params['prc']);
        else
            $price = 0;
        if (isset($params['qty']))
            $quantity = intval($params['qty']);
        else
            $quantity = '';
        if (isset($params['ann']))
            $description = trim($params['ann']);
        else
            $description = '';
        if (isset($params['dsc']))
            $body = trim($params['dsc']);
        else
            $body = '';
        if (isset($params['url']))
            $url = trim($params['url']);
        else
            $url = '';
        if (isset($params['mttl']))
            $meta_title = trim($params['mttl']);
        else
            $meta_title = '';
        if (isset($params['mkwd']))
            $meta_keywords = trim($params['mkwd']);
        else
            $meta_keywords = '';
        if (isset($params['mdsc']))
            $meta_description = trim($params['mdsc']);
        else
            $meta_description = '';
        if (isset($params['enbld']))
            $enabled = trim($params['enbld']);
        else
            $enabled = '';
        if (isset($params['hit']))
            $hit = trim($params['hit']);
        else
            $hit = '';
        if (isset($params['simg']))
            $small_image = trim($params['simg']);
        else
            $small_image = '';
        if (isset($params['limg']))
            $large_image = trim($params['limg']);
        else
            $large_image = '';
        if (isset($params['imgs']))
            $images_string = trim($params['imgs']);
        else
            $images_string = '';
        if (isset($params['kolvo']))
            $kolvo = trim($params['kolvo']);
        else
            $kolvo = 0;
        // , cvet, grup, allsv
        if (isset($params['razmer']))
            $razmer = trim($params['razmer']);
        else
            $razmer = "";
        if (isset($params['cvet']))
            $cvet = trim($params['cvet']);
        else
            $cvet = "";
        if (isset($params['grup']))
            $grup = trim($params['grup']);
        else
            $grup = "";
        if (isset($params['allsv']))
            $allsv = trim($params['allsv']);
        else
            $allsv = "";
        if (isset($params['izbr']))
            $izbr = trim($params['izbr']);
        else
            $izbr = "";
        if (isset($params['bodyp']))
            $bodyp = trim($params['bodyp']);
        else
            $bodyp = "";
        if (isset($params['short_name']))
            $short_name = trim($params['short_name']);
        else
            $short_name = "";
        if (isset($params['max_sale']))
            $max_sale = trim($params['max_sale']);
        else
            $max_sale = "";
        if (isset($params['related']))
            $related = trim($params['related']);
        else
            $related = "";
        if (isset($params['old_price']))
            $old_price = trim($params['old_price']);
        else
            $old_price = "";
        if (isset($params['shop_sclad']))
            $shop_sclad = trim($params['shop_sclad']);
        else
            $shop_sclad = "";
        if (isset($params['shop_makarova']))
            $shop_makarova = trim($params['shop_makarova']);
        else
            $shop_makarova = "";
        if (isset($params['shop_204']))
            $shop_204 = trim($params['shop_204']);
        else
            $shop_204 = "";
        if (isset($params['shop_mira']))
            $shop_mira = trim($params['shop_mira']);
        else
            $shop_mira = "";
        if (isset($params['shop_yog']))
            $shop_yog = trim($params['shop_yog']);
        else
            $shop_yog = "";
        if (isset($params['shop_passaj']))
            $shop_passaj = trim($params['shop_passaj']);
        else
            $shop_passaj = "";

        if ($params['zakaz'] != '')
            $zakaz = trim($params['zakaz']);
        else
            $zakaz = 0;
        $url = translit($model);

        if (isset($params['tags']))
            $tags = explode("#",$params['tags']);
        else
            $tags = array();


        $brand = 0;
        if (isset($params['brand'])) {
            $brand = process_brand(trim($params['brand']));
        }


        //Парсинг тегов






        // Парсинг характеристик
        if (!empty($allsv)) {
            $haracts = explode("|", $allsv);
            $haractsIds = array();
            $haractsNames = array();
            foreach ($haracts as $key => $value) {
                $valueArray = explode("@", $value);
                $haractsIds[] = process_haract($valueArray[0]);
                $haractsNames[] = $valueArray[0];
            }
            $haractsRep = str_replace($haractsNames, $haractsIds, $haracts);
            for ($i = 0; $i < count($haractsRep); $i++) {
                $haractsRep[$i] = explode('@', $haractsRep[$i]);
            }
        }

        $enabled = 1;

        /* ищем по ид, если есть заменяем цену, кол-во цвет и размер */
        $simpla->db->query('SELECT id FROM __variants WHERE external_id=?', $sku);

        $product_id = $simpla->db->result('id');
        $simpla->db->query('SELECT product_id FROM __variants WHERE external_id=?', $sku);
        $real_product_id = $simpla->db->result('product_id');

        $meta_title = $model . " - " . $category;
        $meta_keywords = $category . " " . $model;
        $meta_description = $category . " " . $model;
        $simpla->db->query('SELECT id FROM __products WHERE external_id=? LIMIT 1', $sku);
        $productId = $simpla->db->result('id');
        if ($productId > 0) {


            foreach ($tags as $value) {

                if($value == '')
                {
                    continue;
                }

                $query = $simpla->db->placehold("INSERT IGNORE INTO __tags SET type=?, object_id=?, value=?", 'product', intval($productId), $value);
                $simpla->db->query($query);

                if(!$simpla->tags->get_tag((string)$value,'sdsd'))
                {
                    $tag = new stdClass;
                    $tag->name = $value;

                    $simpla->tags->add_tag($tag);

                }
            }




            $category = str_replace("СПОРТ/", "", $category);
            $category_id = process_category($category);
            $simpla->db->query('SELECT category_id FROM __products_categories WHERE product_id=? LIMIT 1', $productId);
            $categoryIdFromBase = $simpla->db->result('category_id');
            //echo "echos".$category_id." + ".$categoryIdFromBase;
            if (!empty($categoryIdFromBase) && $categoryIdFromBase != "" && $category_id != $categoryIdFromBase) {
                //echo "echoss".$category_id." + ".$categoryIdFromBase;






                $simpla->db->query('UPDATE __products_categories SET category_id=? WHERE product_id=? LIMIT 1', $category_id, $productId);


                update_cat($category_id,$productId);


            }
            if ($categoryIdFromBase == "" || empty($categoryIdFromBase)) {
                //echo "truth".$category_id." + ".$categoryIdFromBase;
                $simpla->db->query('INSERT INTO __products_categories (category_id,product_id,position) VALUES (?,?,?) ', $category_id, $productId, 0);


                //add_cat($category_id,$productId);




            }
        }
        if ($product_id > 0) {
            if ($cvet != "" and $razmer != "")
                $variant_n = $cvet . " / " . $razmer;
            else
                $variant_n = $cvet . $razmer;

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
                add_images($real_product_id, $izbr);
                echo "</div>";
            }
            if (!empty($related)) {
                $related_all = explode(",", $related);

                $related_arr = array();
                foreach ($related_all as $related_item) {
                    $related_item = trim($related_item);
                    $related_arr[] = $related_item;
                }

                $GLOBALS["related_buffer"][] = array(
                    "product_id" => $real_product_id,
                    "relative_product" => $related_arr
                );
            }

            $nameForProduct = $model;
            if ($grup != "") {
                $nameForProduct = $grup;
            }

            $simpla->variants->log('UPDATE __variants SET name=?, price=?, compare_price=?, stock=?,name=?,pod_zakaz=?, max_sale=?, shop_sclad=?, shop_makarova=?, shop_204=?, shop_mira=?, shop_yog=?, shop_passaj=? WHERE external_id=? LIMIT 1'." $model, $price, $old_price, $kolvo, $variant_n, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj, $sku");
            $simpla->db->query('UPDATE __variants SET name=?, price=?, compare_price=?, stock=?,name=?,pod_zakaz=?, max_sale=?, shop_sclad=?, shop_makarova=?, shop_204=?, shop_mira=?, shop_yog=?, shop_passaj=? WHERE external_id=? LIMIT 1', $model, $price, $old_price, $kolvo, $variant_n, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj, $sku);
            $simpla->db->query('UPDATE __products SET name=?, body=?, annotation=?, brand_id=?,pod_zakaz=?, max_sale=? WHERE id=? LIMIT 1', $model, $bodyp, $short_name, $brand, $zakaz, $max_sale, $real_product_id);
            //add_cat($category_id,$real_product_id);
            //echo $category_id."==".$real_product_id."<hr />";
            if (!empty($allsv)) {
                $simpla->db->query('DELETE FROM __options WHERE product_id=?', $productId);

                foreach ($haractsRep as $key => $value) {
                    if (!empty($value[0]) && !empty($value[1])) {
                        $simpla->db->query('INSERT INTO __options (product_id,feature_id,value) VALUES (?,?,?)', $productId, $value[0], $value[1]);
                    }
                }
            }
        } else if ($model != "") {
            // возможно такой товар есть, а это просто вариант.
            if ($grup != "") {
                $simpla->db->query('SELECT product_id FROM __variants WHERE gr=?', $grup);
                $product_id = $simpla->db->result('product_id');
            }

            if ($product_id > 0) {
                if ($cvet != "" and $razmer != "")
                    $variant_n = $cvet . " / " . $razmer;
                else
                    $variant_n = $cvet . $razmer;
                $nameForProduct = $model;
                if ($grup != "") {
                    $nameForProduct = $grup;
                }
                //Обновление статуса под заказ и изображения
                $simpla->db->query('UPDATE __products SET name=?,body=?,annotation=?,brand_id=?,pod_zakaz=?, max_sale=? WHERE id=? LIMIT 1', $nameForProduct, $bodyp, $short_name, $brand, $zakaz, $max_sale, $product_id);
                $query = $simpla->db->placehold("INSERT INTO __variants(name, product_id, sku, price, compare_price, stock, gr, external_id, pod_zakaz, max_sale, shop_sclad, shop_makarova, shop_204, shop_mira, shop_yog, shop_passaj) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $variant_n, $product_id, $sku, $price, $old_price, $kolvo, $grup, $sku, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj);
                $simpla->variants->log($query);
                $simpla->db->query($query);
                $result_category_id = $simpla->db->insert_id();
                $category = str_replace("СПОРТ/", "", $category);
                $category_id = process_category($category);
                $query = $simpla->db->placehold("INSERT INTO __products_categories(product_id,category_id,position) VALUES(?,?,0)", $pro_id, $category_id);
                $simpla->db->query($query);



                add_cat($category_id,$pro_id);




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
                echo "<div style='background-color: #fff;padding:0 10px;width: 105px'>добавили вариант</div>";
                echo "</div>";
                if (!empty($allsv)) {
                    $simpla->db->query('DELETE FROM __options WHERE product_id=?', $productId);

                    foreach ($haractsRep as $key => $value) {
                        if (!empty($value[0]) && !empty($value[1])) {
                            $simpla->db->query('INSERT INTO __options (product_id,feature_id,value) VALUES (?,?,?)', $productId, $value[0], $value[1]);
                        }
                    }
                }
            } else {

                $nameForProduct = $model;
                if ($grup != "") {
                    $nameForProduct = $grup;
                }
                $query = $simpla->db->query("INSERT INTO __products(name, url, body, brand_id, content_title, meta_title, meta_keywords, meta_description, visible, external_id, pod_zakaz, max_sale) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)", $nameForProduct, $url, $bodyp, $short_name, $brand, $nameForProduct, $nameForProduct, $meta_keywords, $meta_description, 1, $sku, $zakaz, $max_sale);



                $pro_id = $simpla->db->insert_id();

                if ($pro_id > 0) {



                    foreach ($tags as $value) {

                        if($value == '')
                        {
                            continue;
                        }

                        $query = $simpla->db->placehold("INSERT IGNORE INTO __tags SET type=?, object_id=?, value=?", 'product', intval($pro_id), $value);
                        $simpla->db->query($query);

                        if(!$simpla->tags->get_tag((string)$value,'sdsd'))
                        {
                            $tag = new stdClass;
                            $tag->name = $value;

                            $simpla->tags->add_tag($tag);

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
                        add_images($pro_id, $izbr);
                        echo "</div>";
                    }
                    if (!empty($related)) {
                        $related_all = explode(",", $related);

                        $related_arr = array();
                        foreach ($related_all as $related_item) {
                            $related_item = trim($related_item);
                            $related_arr[] = $related_item;
                        }

                        $GLOBALS["related_buffer"][] = array(
                            "product_id" => $pro_id,
                            "relative_product" => $related_arr
                        );
                    }
                    echo "</div>";

                    if ($cvet != "" and $razmer != "")
                        $variant_n = $cvet . " / " . $razmer;
                    else
                        $variant_n = $cvet . $razmer;

                    $query = $simpla->db->placehold("INSERT INTO __variants(name, product_id, sku, price, compare_price, stock, gr, external_id, pod_zakaz, max_sale, shop_sclad, shop_makarova, shop_204, shop_mira, shop_yog, shop_passaj) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", $variant_n, $pro_id, $sku, $price, $old_price, $kolvo, $grup, $sku, $zakaz, $max_sale, $shop_sclad, $shop_makarova, $shop_204, $shop_mira, $shop_yog, $shop_passaj);
                    $simpla->db->query($query);
                    $simpla->variants->log($query);
                    $result_category_id = $simpla->db->insert_id();

                    $category = str_replace("СПОРТ/", "", $category);
                    $category_id = process_category($category);

                    $query = $simpla->db->placehold("INSERT INTO __products_categories(product_id,category_id,position) VALUES(?,?,0)", $pro_id, $category_id);
                    $simpla->db->query($query);


                    add_cat($category_id,$pro_id);


                    if (!empty($allsv)) {
                        foreach ($haractsRep as $key => $value) {
                            if (!empty($value[0]) && !empty($value[1])) {
                                $simpla->db->query('INSERT INTO __options (product_id,feature_id,value) VALUES (?,?,?)', $pro_id, $value[0], $value[1]);

                                $simpla->db->query('INSERT INTO __categories_features (category_id,feature_id) VALUES (?,?)', $category_id, $value[0]);
                            }
                        }
                    }
                }
            }
        }

        // $cols_order = "ctg, sku, dsc, sclad, kolvo, prc, sht";
    }
}

///////////////////////////////////////////
///////////////////////////////////////////
function import_csv($fname, $cols_order, $delimiter) {
    $handle = fopen($fname, "r");
    if (!$handle) {
        echo 'Не могу загрузить файл. Проверьте настройки сервера';
    } else {
        // Максимальное время выполнения скрипта
        $max_time = @ini_get('max_execution_time');
        if (!$max_time)
            $max_time = 30;

        // Порядок колонок
        $temp = split(',', $cols_order);
        $i = 0;
        foreach ($temp as $tmp) {
            $columns[trim($tmp)] = $i;
            $i++;
        }
        if (!((isset($columns['name']) && isset($columns['ctg'])) || isset($columns['grup']) || isset($columns['sku']))) {
            echo 'Среди колонок должен присутствовать артикул или категория и название товара';
            return false;
        }

        $start_time = microtime(true);
        $time_elapsed = 0;
        $cols = true;

        # Идем по всем строкам
        while ($cols) {
            $cols = fgetcsv($handle, $csv_line_maxlength, $delimiter);
            /* ?><pre><?print_r($cols)?></pre><?	 */
            foreach ($columns as $name => $index) {
                if (isset($cols[$index]))
                    $values[$name] = $cols[$index];
                else
                    $values[$name] = '';
            }
            /* ?><pre><?print_r($values)?></pre><? */
            $process_product($values);

            $current_time = microtime(true);
            $time_elapsed = $current_time - $start_time;
        }
        fclose($handle);
    }
}

function translit($text) {
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

function translit_img($text) {
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

function clear_dir($dir) {
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle)))
            if ($file != "." && $file != "..")
                unlink($dir . $file);
        closedir($handle);
    }
}

function add_images($product_id, $izbr) {

    $simpla = new Simpla();
    $izbr_all = explode(",", $izbr);

    $simpla->db->query("SELECT id, product_id FROM __variants WHERE product_id=?", $product_id);
    $result_2 = $simpla->db->results();

    $image_exist = 0;
    foreach ($result_2 as $variant) {
        $simpla->db->query("SELECT * FROM __images WHERE product_id=?", $variant->product_id);
        $result3 = $simpla->db->result();
        if ($result3) {
            $image_exist += 1;
        }
    }

    foreach ($izbr_all as $izbr_item) {
        $old_izbr_item = trim($izbr_item);
        $izbr_item = translit_img(trim($izbr_item));

        $fileimg = $izbr_item;
        if (preg_match('/(.+)\.([^\.]+)$/', $fileimg, $matches)) {

            // $path = $GLOBALS["server_path"] . "images1c/".iconv("utf-8","cp1251",$old_izbr_item);
            $path = $GLOBALS["server_path"] . "images1c/" . $old_izbr_item;
            $path_2 = $GLOBALS["server_path"] . "files/originals/" . $izbr_item;

            $resize_dir = $GLOBALS["server_path"] . "files/products/";

            $path_parts = pathinfo($path);
            $path_parts_2 = pathinfo($path_2);

            // if (copy($path, $path_2)) {
            //     if (file_exists($path_2)) {
            //         $query = $simpla->db->query("INSERT INTO __images(product_id, filename) VALUES(?,?)", $product_id, $izbr_item);
            //         echo "<span style='color: green; font-weight: bold;'>Изображение '" . $path_parts['basename'] . " " .filesize($path). " " . "' добавлено как '" . $path_parts_2['basename'] . " " .filesize($path). "'</span><br/>";
            //     }
            // } else {
            //     echo "<span style='color: red; font-weight: bold;'>Изображение '" . $path_parts['basename'] . "' не добавлено</span><br/>";
            // }
//                    if((filesize($path) != filesize($path_2))) {
//                        if (copy($path, $path_2)) {
//                            if (file_exists($path_2)) {
//
//                                echo "<span style='color: orange; font-weight: bold;'>Изображение " . $path_parts['basename'] . " " .filesize($path). " " . " заменено на " . $path_parts_2['basename'] . " " .filesize($path_2). "</span><br/>";
//                                $resize_files = scandir($resize_dir);
//                                foreach ($resize_files as $resize_file_name) {
//                                    if(substr_count($resize_file_name, $path_parts_2['filename'])) {
//                                        unlink("$resize_dir/$resize_file_name");
//                                        echo "Изображение $resize_file_name удалено<br/>";
//                                    }
//                                }
//                            }
//                        } else {
//                            echo "<span style='color: red; font-weight: bold;'>Изображение '" . $path_parts['basename'] . "' не заменено</span><br/>";
//                        }
//                    }
//                    SELECT v.product_id, i.filename FROM s_variants v LEFT JOIN s_images i ON v.product_id = i.product_id WHERE v.product_id='71' AND i.filename=''

            if ((filesize($path) != filesize($path_2))) {
                $resize_files = scandir($resize_dir);
                foreach ($resize_files as $resize_file_name) {
                    if (substr_count($resize_file_name, $path_parts_2['filename'])) {
                        unlink("$resize_dir/$resize_file_name");
                        echo "<span style='color: orange; font-weight: bold;'>Изображение $resize_file_name удалено из products </span><br/>";
                    }
                }
            }

            $simpla->db->query("SELECT * FROM __images WHERE product_id=? AND filename=?", $product_id, $izbr_item);
            $result = $simpla->db->result();

            if ((!$result && $image_exist == 0)) {

                if (copy($path, $path_2)) {
                    if (file_exists($path_2)) {
                        $query = $simpla->db->query("INSERT INTO __images(product_id, filename) VALUES(?,?)", $product_id, $izbr_item);

                        echo "<span style='color: green; font-weight: bold;'>Изображение '" . $path_parts['basename'] . " " . filesize($path) . " " . "' добавлено как '" . $path_parts_2['basename'] . " " . filesize($path) . "'</span><br/>";
                    }
                } else {
                    echo "<span style='color: red; font-weight: bold;'>Изображение '" . $path_parts['basename'] . "' не добавлено</span><br/>";
                }
            }
        }
    }
}

function process_get_commments() {
    $sim = new Simpla();
    //    $sim->db->query("SELECT c.*, p.external_id FROM __comments as c INNER JOIN __products as p on c.object_id = p.id ORDER BY c.id");
    $sim->db->query("SELECT c.* FROM __comments as c ORDER BY c.id");
    $result = $sim->db->results();

    //    $cur_date = date("d-m-Y(h-i-s)");

    $file = $GLOBALS["server_path"] . 'price/comments.txt';

    //Пишем содержимое в файл
    file_put_contents($file, serialize($result));
}

function process_add_comments() {
    $sim = new Simpla();
    $file = $GLOBALS["server_path"] . 'price/comments.txt';

    $data = unserialize(file_get_contents($file));

    print_r($data);
    foreach ($data as $comment) {
        print_r($comment);
        if ($comment->type == "product") {
            $sim->db->query("SELECT id FROM __products WHERE external_id = ?", $comment->object_sku);
            $result_id = $sim->db->result("id");
            if (!$result_id) {
                $product_id = 0;
            } else {
                $product_id = $result_id;
            }
        } else {
            $product_id = $comment->object_id;
        }
        $sim->db->query('INSERT INTO __comments (date, ip, object_id, object_sku, name, email, text, rating, type, approved) VALUES (?,?,?,?,?,?,?,?,?,?)', $comment->date, $comment->ip, $product_id, $comment->object_sku, $comment->name, $comment->email, $comment->text, $comment->rating, $comment->type, $comment->approved);
    }
}

function process_update_comments() {
    $sim = new Simpla();

    $sim->db->query("SELECT id, object_sku FROM __comments WHERE object_id = '0'");
    $result = $sim->db->results();

    print_r($result);

    foreach ($result as $item) {
        $sim->db->query("SELECT id FROM __products WHERE external_id = ?", $item->object_sku);
        $product_id = $sim->db->result("id");

        if ($product_id) {
            $sim->db->query('UPDATE __comments SET object_id = ? WHERE id = ? LIMIT 1', $product_id, $item->id);
        }
    }
}

function process_clear_comments() {
    $sim = new Simpla();

    $sim->db->query('TRUNCATE __comments');
}
?>