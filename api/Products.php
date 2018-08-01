<?php

/**
 * Работа с товарами
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */
require_once('Simpla.php');

class Products extends Simpla {

    /**
     * Функция возвращает товары
     * Возможные значения фильтра:
     * id - id товара или их массив
     * category_id - id категории или их массив
     * brand_id - id бренда или их массив
     * page - текущая страница, integer
     * limit - количество товаров на странице, integer
     * sort - порядок товаров, возможные значения: position(по умолчанию), name, price
     * keyword - ключевое слово для поиска
     * features - фильтр по свойствам товара, массив (id свойства => значение свойства)
     */
    public function get_products($filter = array()) {
        // По умолчанию
        $page = 1;
        $category_id_filter = '';
        $brand_id_filter = '';
        $product_id_filter = '';
        $features_filter = '';
        $keyword_filter = '';
        $visible_filter = '';
        $visible_filter = '';
        $is_featured_filter = '';
        $discounted_filter = '';
        $in_stock_filter = '';
        $group_by = '';
        $prices = '';
        $order = 'p.name';
        $imagesc = '';
        $sql_limit = '';

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        if (isset($limit) && isset($page))
            $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        if (!empty($filter['id']))
            $product_id_filter = $this->db->placehold('AND p.id in(?@)', (array) $filter['id']);

        if (!empty($filter['category_id'])) {
            $category_id_filter = $this->db->placehold('INNER JOIN __products_categories pc ON pc.product_id = p.id AND pc.category_id in(?@)', (array) $filter['category_id']);
            $group_by = "GROUP BY p.id";
        }

        if (!empty($filter['brand_id']))
            $brand_id_filter = $this->db->placehold('AND p.brand_id in(?@)', (array) $filter['brand_id']);

        if (!empty($filter['featured']))
            $is_featured_filter = $this->db->placehold('AND p.featured=?', intval($filter['featured']));

        if (!empty($filter['discounted']))
            $discounted_filter = $this->db->placehold('AND (SELECT 1 FROM __variants pv WHERE pv.product_id=p.id AND pv.compare_price>0 LIMIT 1) = ?', intval($filter['discounted']));

        if (!empty($filter['in_stock']))//AND pv.price>0
            $in_stock_filter = $this->db->placehold('AND (SELECT 1 FROM __variants pv WHERE pv.product_id=p.id  AND ((pv.stock IS NULL AND pv.pod_zakaz=1) OR (pv.stock>0 AND pv.pod_zakaz=1) OR (pv.stock>0 AND pv.price>0) OR pv.pod_zakaz=1) LIMIT 1) = ?', intval($filter['in_stock']));

        if (!empty($filter['visible']))
            $visible_filter = $this->db->placehold('AND p.visible=?', intval($filter['visible']));

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (p.name LIKE "%' . mysql_real_escape_string(trim($keyword)) . '%" OR p.meta_keywords LIKE "%' . mysql_real_escape_string(trim($keyword)) . '%" OR p.external_id="' . mysql_real_escape_string($keyword) . '") ');
        }

		/* chpu_filter */
        /*if(!empty($filter['features']) && !empty($filter['features']))
			foreach($filter['features'] as $feature=>$value)
				$features_filter .= $this->db->placehold('AND p.id in (SELECT product_id FROM __options WHERE feature_id=? AND value=? ) ', $feature, $value);*/
        if (!empty($filter['features']) && !empty($filter['features'])) {
            foreach ($filter['features'] as $feature => $value) {
                $features_filter .= $this->db->placehold('AND p.id in (SELECT product_id FROM __options WHERE feature_id=? AND translit in(?@) ) ', $feature, (array)$value);
            }
        }

        $price_filter = '';
        $variant_join = '';
        if(isset($filter['price'])){
            if(!empty($filter['price']['min']))
                $price_filter .= $this->db->placehold(' AND pv.price>= ? ', trim($filter['price']['min']));
            if(!empty($filter['price']['max']))
                $price_filter .= $this->db->placehold(' AND pv.price<= ? ', trim($filter['price']['max']));
            $variant_join = 'LEFT JOIN __variants pv ON pv.product_id = p.id';
        }
        /* chpu_filter /*/

        if (!empty($filter['sort']))
            switch ($filter['sort']) {
                case 'position':
                    $order = 'p.position DESC';
                    break;
                case 'created':
                    $order = 'p.created DESC';
                    break;
                case 'priceUp':
                    //$order = 'pv.price IS NULL, pv.price=0, pv.price';
                    $order = '(SELECT pv.price FROM __variants pv WHERE (pv.stock IS NULL OR pv.stock>0) AND p.id = pv.product_id AND pv.position=(SELECT MIN(position) FROM __variants WHERE (stock>0 OR stock IS NULL) AND product_id=p.id LIMIT 1) LIMIT 1) ';
                    break;
                case 'priceDown':
                    //$order = 'pv.price IS NULL, pv.price=0, pv.price';
                    $order = '(SELECT pv.price FROM __variants pv WHERE (pv.stock IS NULL OR pv.stock>0) AND p.id = pv.product_id AND pv.position=(SELECT MIN(position) FROM __variants WHERE (stock>0 OR stock IS NULL) AND product_id=p.id LIMIT 1) LIMIT 1) DESC';
                    break;
                case 'name':
                    $order = 'p.name';
                    break;
                case 'nameDesc':
                    $order = 'p.name DESC';
                    break;
                case 'discount':
                    $variant_join = $variant_join ? $variant_join : 'LEFT JOIN __variants pv ON pv.product_id = p.id';
                    $order = '(pv.compare_price - pv.price) / pv.price * 100 DESC, pv.position DESC';
                    break;
            }

        $query = "SELECT  
					p.id,
					p.url,
					p.brand_id,
					p.name,
					p.annotation,
					p.body,
					p.attachment_files_one,
					p.video,
					p.harakt,
					p.position,
					p.created as created,
					p.visible, 
					p.featured,
                    p.rating,
                    p.votes,
                    p.images as imagesc,                
					p.content_title,
					p.meta_title, 
					p.meta_keywords, 
					p.meta_description, 
					b.name as brand,
					b.url as brand_url,
					p.pod_zakaz,
					p.max_sale,
					p.featured,
                    p.rating,
                    p.votes
				FROM __products p		
				$category_id_filter 
				/* chpu_filter */
                $variant_join
                /* chpu_filter /*/
				LEFT JOIN __brands b ON p.brand_id = b.id
				WHERE 
					1
					$product_id_filter
					$brand_id_filter
					$features_filter
					$keyword_filter
					$is_featured_filter
					$discounted_filter
					$in_stock_filter
					$visible_filter
					/* chpu_filter */
                    $price_filter
                    /* chpu_filter /*/
				$group_by
				ORDER BY $order
					$sql_limit";

        $query = $this->db->placehold($query);
        $this->db->query($query);



        $products = $this->db->results();

        foreach ($products as $product) {
            $product->cat_url = $this->categories->get_product_categories_url($product->id);
            if (!empty($product->imagesc)) {
                $imagesc = "";
                $images = explode(",", $product->imagesc);
                $path = "./images1c/";
                if (count($images) != 0) {
                    foreach ($images as $key => $value) {
                        if (!file_exists($path . trim($value))) {
                            
                        } else {
                            $counter++;
                            $imagesc[] = trim($value);
                        }
                    }
                }


                $product->imagesc = $imagesc[0];
            }
        }

        return $products;
    }

    /**
     * Функция возвращает количество товаров
     * Возможные значения фильтра:
     * category_id - id категории или их массив
     * brand_id - id бренда или их массив
     * keyword - ключевое слово для поиска
     * features - фильтр по свойствам товара, массив (id свойства => значение свойства)
     */
    public function count_products($filter = array()) {
        $category_id_filter = '';
        $brand_id_filter = '';
        $keyword_filter = '';
        $visible_filter = '';
        $is_featured_filter = '';
        $discounted_filter = '';
        $features_filter = '';
        $prices = '';

        if (!empty($filter['category_id']))
            $category_id_filter = $this->db->placehold('INNER JOIN __products_categories pc ON pc.product_id = p.id AND pc.category_id in(?@)', (array) $filter['category_id']);

        if (!empty($filter['brand_id']))
            $brand_id_filter = $this->db->placehold('AND p.brand_id in(?@)', (array) $filter['brand_id']);

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (p.name LIKE "%' . mysql_real_escape_string(trim($keyword)) . '%" OR p.meta_keywords LIKE "%' . mysql_real_escape_string(trim($keyword)) . '%" OR p.external_id="' . mysql_real_escape_string($keyword) . '") ');
        }

        if (!empty($filter['featured']))
            $is_featured_filter = $this->db->placehold('AND p.featured=?', intval($filter['featured']));

        if (!empty($filter['discounted']))
            $discounted_filter = $this->db->placehold('AND (SELECT 1 FROM __variants pv WHERE pv.product_id=p.id AND pv.compare_price>0 LIMIT 1) = ?', intval($filter['discounted']));

		if(isset($filter['visible']))
			$visible_filter = $this->db->placehold('AND p.visible=?', intval($filter['visible']));


		/* chpu_filter */
        /*if(!empty($filter['features']) && !empty($filter['features']))
			foreach($filter['features'] as $feature=>$value)
				$features_filter .= $this->db->placehold('AND p.id in (SELECT product_id FROM __options WHERE feature_id=? AND value=? ) ', $feature, $value);*/
        if(!empty($filter['features']) && !empty($filter['features']))
            foreach($filter['features'] as $feature=>$value)
                $features_filter .= $this->db->placehold('AND p.id in (SELECT product_id FROM __options WHERE feature_id=? AND translit in(?@) ) ', $feature, (array)$value);

        $price_filter = '';
        $variant_join = '';
        $select = 'count(distinct p.id) as count';
        if(isset($filter['get_price'])){
            $variant_join = 'LEFT JOIN __variants pv ON pv.product_id = p.id';
            $select = 'MIN(pv.price) as min, MAX(pv.price) as max';
        }elseif(isset($filter['price'])){
            if(!empty($filter['price']['min']))
                $price_filter .= $this->db->placehold(' AND pv.price>= ? ', trim($filter['price']['min']));
            if(!empty($filter['price']['max']))
                $price_filter .= $this->db->placehold(' AND pv.price<= ? ', trim($filter['price']['max']));
            $variant_join = 'LEFT JOIN __variants pv ON pv.product_id = p.id';
        }
        /* chpu_filter /*/

		$query = "SELECT /* chpu_filter *//*count(distinct p.id) as count*/$select/* chpu_filter /*/
				FROM __products AS p
				$category_id_filter
				/* chpu_filter */
                $variant_join
                /* chpu_filter /*/
				WHERE 1
					$brand_id_filter
					$keyword_filter
					$is_featured_filter
					$discounted_filter
					$visible_filter
					$features_filter 
					/* chpu_filter */
                    $price_filter
                    /* chpu_filter /*/";

		$this->db->query($query);
        /* chpu_filter_extended */
        if(isset($filter['get_price']))
            return $this->db->result();
        else/* chpu_filter_extended /*/
		    return $this->db->result('count');
	}

    /**
     * Функция возвращает товар по id
     * @param	$id
     * @retval	object
     */
    public function get_product($id) {
        if (is_int($id))
            $filter = $this->db->placehold('p.id = ?', $id);
        else
            $filter = $this->db->placehold('p.url = ?', $id);

        $query = $this->db->placehold("SELECT DISTINCT
					p.id,
					p.url,
					p.brand_id,
					p.name,
					p.annotation,
					p.body,
					p.attachment_files_one,
					p.video,
					p.harakt,
					p.position,
					p.created as created,
					p.visible, 
					p.featured,
                    p.rating,
                    p.votes,
					p.content_title, 
					p.meta_title, 
					p.meta_keywords, 
					p.meta_description,
					p.images,
					p.pod_zakaz,
					p.max_sale,
					p.featured,
                    p.rating,
                    p.votes,
                    p.external_id
				FROM __products AS p
                LEFT JOIN __brands b ON p.brand_id = b.id
                WHERE $filter
                GROUP BY p.id
                LIMIT 1", intval($id));
        $this->db->query($query);
        $product = $this->db->result();
        if (!empty($product->images)) {
            $imagesc = explode(",", $product->images);
        }
        if (isset($product->id) && empty($product->images)) {
            $product->images = $this->get_images(array('product_id' => $product->id));
            $product->image = $product->images[0];
        }

        if ($product) {
            $product->cat_url = $this->categories->get_product_categories_url($product->id);

            $variants = array();
            foreach ($this->variants->get_variants(array('product_id' => $product->id, 'in_stock' => true)) as $v) {
                $variants[$v->id] = $v;
            }

            $product->variants2 = $variants;



            // Вариант по умолчанию
            if (($v_id = $this->request->get('variant', 'integer')) > 0 && isset($variants[$v_id]))
                $product->variant2 = $variants[$v_id];
            else
                $product->variant2 = reset($variants);
        }


        return $product;
    }

    public function update_product($id, $product) {
        $query = $this->db->placehold("UPDATE __products SET ?% WHERE id in (?@) LIMIT ?", $product, (array) $id, count((array) $id));

        if ($this->db->query($query))
            return $id;
        else
            return false;
    }

    public function add_product($product) {
        $product = (array) $product;

        if (empty($product['url'])) {
            $product['url'] = preg_replace("/[\s]+/ui", '-', $product['name']);
            $product['url'] = strtolower(preg_replace("/[^0-9a-zа-я\-]+/ui", '', $product['url']));
        }

        // Если есть товар с таким URL, добавляем к нему число
        while ($this->get_product((string) $product['url'])) {
            if (preg_match('/(.+)_([0-9]+)$/', $product['url'], $parts))
                $product['url'] = $parts[1] . '_' . ($parts[2] + 1);
            else
                $product['url'] = $product['url'] . '_2';
        }

        if ($this->db->query("INSERT INTO __products SET ?%", $product)) {
            $id = $this->db->insert_id();
            $this->db->query("UPDATE __products SET position=id WHERE id=?", $id);
            return $id;
        } else
            return false;
    }

    /*
     *
     * Удалить товар
     *
     */

    public function delete_product($id) {
        if (!empty($id)) {
            // Удаляем варианты
            $variants = $this->variants->get_variants(array('product_id' => $id));
            foreach ($variants as $v)
                $this->variants->delete_variant($v->id);

            // Удаляем файл_one
            $this->delete_attachment_one($id);
            $query = $this->db->placehold("DELETE FROM __products WHERE id=? LIMIT 1", $id);

            // Удаляем изображения
            $images = $this->get_images(array('product_id' => $id));
            foreach ($images as $i)
                $this->delete_image($i->id);

            // Удаляем категории
            $categories = $this->categories->get_categories(array('product_id' => $id));
            foreach ($categories as $c)
                $this->categories->delete_product_category($id, $c->id);

            // Удаляем свойства
            $options = $this->features->get_options(array('product_id' => $id));
            foreach ($options as $o)
                $this->features->delete_option($id, $o->feature_id);

            // Удаляем связанные товары
            $related = $this->get_related_products($id);
            foreach ($related as $r)
                $this->delete_related_product($id, $r->related_id);

            // Удаляем отзывы
            $comments = $this->comments->get_comments(array('object_id' => $id, 'type' => 'product'));
            foreach ($comments as $c)
                $this->comments->delete_comment($c->id);

            // Удаляем из покупок
            $this->db->query('UPDATE __purchases SET product_id=NULL WHERE product_id=?', intval($id));

            // Удаляем товар
            $query = $this->db->placehold("DELETE FROM __products WHERE id=? LIMIT 1", intval($id));
            if ($this->db->query($query))
                return true;
        }
        return false;
    }

    public function duplicate_product($id) {
        $product = $this->get_product($id);
        $product->id = null;
        $product->created = null;

        // Сдвигаем товары вперед и вставляем копию на соседнюю позицию
        $this->db->query('UPDATE __products SET position=position+1 WHERE position>?', $product->position);
        $new_id = $this->products->add_product($product);
        $this->db->query('UPDATE __products SET position=? WHERE id=?', $product->position + 1, $new_id);

        // Очищаем url
        $this->db->query('UPDATE __products SET url="" WHERE id=?', $new_id);

        // Дублируем категории
        $categories = $this->categories->get_product_categories($id);
        foreach ($categories as $c)
            $this->categories->add_product_category($new_id, $c->category_id);

        // Дублируем изображения
        $images = $this->get_images(array('product_id' => $id));
        foreach ($images as $image)
            $this->add_image($new_id, $image->filename);

        // Дублируем варианты
        $variants = $this->variants->get_variants(array('product_id' => $id));
        foreach ($variants as $variant) {
            $variant->product_id = $new_id;
            unset($variant->id);
            if ($variant->infinity)
                $variant->stock = null;
            unset($variant->infinity);
            $this->variants->add_variant($variant);
        }

        // Дублируем свойства
        $options = $this->features->get_options(array('product_id' => $id));
        foreach ($options as $o)
            $this->features->update_option($new_id, $o->feature_id, $o->value);

        // Дублируем связанные товары
        $related = $this->get_related_products($id);
        foreach ($related as $r)
            $this->add_related_product($new_id, $r->related_id);


        return $new_id;
    }

    function get_related_products($product_id = array()) {
        if (empty($product_id))
            return array();

        $product_id_filter = $this->db->placehold('AND product_id in(?@)', (array) $product_id);

        $query = $this->db->placehold("SELECT product_id, related_id, position
					FROM __related_products
					WHERE 
					1
					$product_id_filter   
					ORDER BY position       
					");

        $this->db->query($query);
        return $this->db->results();
    }

    // Функция возвращает связанные товары
    public function add_related_product($product_id, $related_id, $position = 0) {
        $query = $this->db->placehold("INSERT IGNORE INTO __related_products SET product_id=?, related_id=?, position=?", $product_id, $related_id, $position);
        $this->db->query($query);
        return $related_id;
    }

    // Удаление связанного товара
    public function delete_related_product($product_id, $related_id) {
        $query = $this->db->placehold("DELETE FROM __related_products WHERE product_id=? AND related_id=? LIMIT 1", intval($product_id), intval($related_id));
        $this->db->query($query);
    }

    function get_images($filter = array()) {
        $product_id_filter = '';
        $group_by = '';

        if (!empty($filter['product_id']))
            $product_id_filter = $this->db->placehold('AND i.product_id in(?@)', (array) $filter['product_id']);

        // images
        $query = $this->db->placehold("SELECT i.id, i.product_id, i.name, i.filename, i.position
									FROM __images AS i WHERE 1 $product_id_filter $group_by ORDER BY i.product_id, i.position");
        $this->db->query($query);
        return $this->db->results();
    }

    public function add_image($product_id, $filename, $name = '') {
        $query = $this->db->placehold("SELECT id FROM __images WHERE product_id=? AND filename=?", $product_id, $filename);
        $this->db->query($query);
        $id = $this->db->result('id');
        if (empty($id)) {
            $query = $this->db->placehold("INSERT INTO __images SET product_id=?, filename=?", $product_id, $filename);
            $this->db->query($query);
            $id = $this->db->insert_id();
            $query = $this->db->placehold("UPDATE __images SET position=id WHERE id=?", $id);
            $this->db->query($query);
        }
        return($id);
    }

    public function update_image($id, $image) {

        $query = $this->db->placehold("UPDATE __images SET ?% WHERE id=?", $image, $id);
        $this->db->query($query);

        return($id);
    }

    /*
     *
     * Удаление файла_one
     * @param $id
     *
     */

    public function delete_attachment_one($id) {
        $query = $this->db->placehold("SELECT attachment_files_one FROM __products WHERE id=?", intval($id));
        $this->db->query($query);
        $attachment_files_one = $this->db->result('attachment_files_one');
        if (!empty($attachment_files_one)) {
            $query = $this->db->placehold("UPDATE __products SET attachment_files_one=NULL WHERE id=?", $id);
            $this->db->query($query);
            $query = $this->db->placehold("SELECT count(*) as count FROM __products WHERE attachment_files_one=? LIMIT 1", $attachment_files_one);
            $this->db->query($query);
            $count = $this->db->result('count');
            if ($count == 0) {
                @unlink($this->config->root_dir . $this->config->documents_dir . $attachment_files_one);
            }
        }
    }

    public function delete_image($id) {
        $query = $this->db->placehold("SELECT filename FROM __images WHERE id=?", $id);
        $this->db->query($query);
        $filename = $this->db->result('filename');
        $query = $this->db->placehold("DELETE FROM __images WHERE id=? LIMIT 1", $id);
        $this->db->query($query);
        $query = $this->db->placehold("SELECT count(*) as count FROM __images WHERE filename=? LIMIT 1", $filename);
        $this->db->query($query);
        $count = $this->db->result('count');
        if ($count == 0) {
            $file = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            // Удалить все ресайзы
            $rezised_images = glob($this->config->root_dir . $this->config->resized_images_dir . $file . "*." . $ext);
            if (is_array($rezised_images))
                foreach (glob($this->config->root_dir . $this->config->resized_images_dir . $file . "*." . $ext) as $f)
                    @unlink($f);

            @unlink($this->config->root_dir . $this->config->original_images_dir . $filename);
        }
    }

    /*
     *
     * Следующий товар
     *
     */

    public function get_next_product($id) {
        $this->db->query("SELECT position FROM __products WHERE id=? LIMIT 1", $id);
        $position = $this->db->result('position');

        $this->db->query("SELECT pc.category_id FROM __products_categories pc WHERE product_id=? ORDER BY position LIMIT 1", $id);
        $category_id = $this->db->result('category_id');

        $query = $this->db->placehold("SELECT id FROM __products p, __products_categories pc
										WHERE pc.product_id=p.id AND p.position>? 
										AND pc.position=(SELECT MIN(pc2.position) FROM __products_categories pc2 WHERE pc.product_id=pc2.product_id)
										AND pc.category_id=? 
										AND p.visible ORDER BY p.position limit 1", $position, $category_id);
        $this->db->query($query);

        return $this->get_product((integer) $this->db->result('id'));
    }

    /*
     *
     * Предыдущий товар
     *
     */

    public function get_prev_product($id) {
        $this->db->query("SELECT position FROM __products WHERE id=? LIMIT 1", $id);
        $position = $this->db->result('position');

        $this->db->query("SELECT pc.category_id FROM __products_categories pc WHERE product_id=? ORDER BY position LIMIT 1", $id);
        $category_id = $this->db->result('category_id');

        $query = $this->db->placehold("SELECT id FROM __products p, __products_categories pc
										WHERE pc.product_id=p.id AND p.position<? 
										AND pc.position=(SELECT MIN(pc2.position) FROM __products_categories pc2 WHERE pc.product_id=pc2.product_id)
										AND pc.category_id=? 
										AND p.visible ORDER BY p.position DESC limit 1", $position, $category_id);
        $this->db->query($query);

        return $this->get_product((integer) $this->db->result('id'));
    }

    function get_min_price($str) {
        $query = "SELECT  
						price
					FROM __variants		
					WHERE 
						product_id = $str
					ORDER BY price LIMIT 1";

        $query = $this->db->placehold($query);
        $this->db->query($query);

        return $this->db->result('price');
    }

    function get_equally_price($str) {
        $query = "SELECT  
                            price
                        FROM __variants		
                        WHERE 
                            product_id = $str
                        ORDER BY price";

        $query = $this->db->placehold($query);
        $this->db->query($query);
        $all_price = $this->db->results();
        $equally_price = $all_price[0]->price;

        foreach ($all_price as $price) {
            if ((int) $price->price != (int) $equally_price) {
                $equally_price = 0;
                break;
            }
        }
        return $equally_price;
    }

    function get_min_compare_price($str) {
        $query = "SELECT  
                            compare_price
                        FROM __variants		
                        WHERE 
                            product_id = $str
                        ORDER BY compare_price LIMIT 1";

        $query = $this->db->placehold($query);
        $this->db->query($query);

        return $this->db->result('compare_price');
    }

    function get_equally_compare_price($str) {
        $query = "SELECT  
                                compare_price
                            FROM __variants		
                            WHERE 
                                product_id = $str
                            ORDER BY compare_price";

        $query = $this->db->placehold($query);
        $this->db->query($query);
        $all_compare_price = $this->db->results();
        $equally_compare_price = $all_compare_price[0]->compare_price;

        foreach ($all_compare_price as $compare_price) {
            if ((int) $compare_price->compare_price != (int) $equally_compare_price) {
                $equally_compare_price = 0;
                break;
            }
        }
        return $equally_compare_price;
    }

    /*
     *
     * Добавляем теги
     *
     */

    public function add_tags($type, $object_id, $values) {
        $tags = explode(',', $values);
        foreach ($tags as $value) {
            $query = $this->db->placehold("INSERT IGNORE INTO __tags SET type=?, object_id=?, value=?", $type, intval($object_id), $value);
            
            
            $this->db->query($query);
        }
        return count($tags);
    }

    /*
     *
     * Удаляем все теги
     *
     */

    public function delete_tags($type, $object_id) {
        $query = $this->db->placehold("DELETE FROM __tags WHERE type=? AND object_id=?", $type, intval($object_id));
        $this->db->query($query);
    }

    /*
     *
     * Получаем список тегов
     *
     */

    public function get_tags($filter = array()) {
        $type_filter = '';
        $object_id_filter = '';
        $keyword_filter = '';
        $group = '';

        if (isset($filter['group']))
            $group = 'GROUP BY t.value';

        if (isset($filter['type']))
            $type_filter = $this->db->placehold('AND type=?', $filter['type']);

        if (isset($filter['object_id']))
            $object_id_filter = $this->db->placehold('AND object_id in(?@)', (array) $filter['object_id']);

        if (isset($filter['keyword'])) {
            $keywords = explode(',', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $kw = trim($keyword);
                $keyword_filter .= " AND (t.value LIKE '%$kw%' OR d.url LIKE '%$kw%')";
            }
        }

        $query = $this->db->placehold("SELECT type, object_id, value,url FROM __tags as t LEFT JOIN __tags_details as d ON t.value = d.name WHERE 1 $object_id_filter $type_filter $keyword_filter $group ORDER BY value");

        $this->db->query($query);
        $res = $this->db->results();

        return $res;
    }

    /*
     *
     * Получаем детали тега
     *
     */

    public function get_tag($value) {
        $query = $this->db->placehold("SELECT * FROM __tags_details WHERE name=? OR url = ? LIMIT 1", trim($value),trim($value));
        
        $this->db->query($query);
        return $this->db->result();
    }

    /*
     *
     * Обновление тега(ов)
     * @param $brand
     *
     */

    public function update_tag($value, $tag) {
        $exist = $this->get_tag($value);
        if (!empty($exist)) {
            $query = $this->db->placehold("UPDATE __tag_details SET ?% WHERE value=? LIMIT 1", $tag, trim($value));
            $this->db->query($query);
        } else {
            $this->db->query("INSERT INTO __tag_details SET ?%", $tag);
            $value = $tag->value;
        }
        return $value;
    }

    /*
     *
     * Удаление тега
     * @param $id
     *
     */

    public function delete_tag($value) {
        $query = $this->db->placehold("DELETE FROM __tag_details WHERE value=? LIMIT 1", $value);
        $this->db->query($query);
        $query = $this->db->placehold("DELETE FROM __tags WHERE value=?", $value);
        $this->db->query($query);
    }

    

}
