<?PHP

/**
 * Simpla CMS
 *
 * @copyright     2012-2014 Redline Studio
 * @link         http://simplashop.com
 * @author         Artiom Mitrofanov
 *
 */

require_once('View.php');

class TagsView extends View
{
	public function fetch()
	{
        // Если задано ключевое слово
            $tagsValue = false;
        $keyword = $this->request->get('keyword');
        if (empty($keyword))
            $this->design->assign('tags', $this->products->get_tags(array('group'=>1)));
        else {
            $this->design->assign('keyword', $keyword);  
            $tags = $this->products->get_tags(array('keyword'=>$keyword));
            
            $tagsValue = $this->products->get_tag($keyword);
            
            if($tagsValue)
            {
                $this->design->assign('keyword', $tagsValue->name); 
                
            }
            
            
            
            // Выбирает объекты, которые привязаны к тегу:
            $products_ids = array();
            $posts_ids = array();
            foreach($tags as $tag)
            {
                if($tag->type == 'product')
                    $products_ids[] = $tag->object_id;
                if($tag->type == 'blog')
                    $posts_ids[] = $tag->object_id;
            }
            
            if(count($products_ids) > 0) {
                $products = array();
                $products_ids_v = array();
                foreach($this->products->get_products(array('id'=>$products_ids,'visible'=>1,'in_stock'=>1)) as $p)
                    $products[$p->id] = $p;
                    $products_ids_v[$p->id]=$p->id;
                    
                // Выбираем варианты товаров



                $variants = $this->variants->get_variants(array('product_id'=>$products_ids_v));
                
                // Для каждого варианта
                foreach($variants as &$variant)
                {
                    // добавляем вариант в соответствующий товар



                    $products[$variant->product_id]->variants[] = $variant;



                }
                
                // Выбираем изображения товаров
                $images = $this->products->get_images(array('product_id'=>$products_ids_v));
                foreach($images as $image)
                    $products[$image->product_id]->images[] = $image;

                foreach($products as &$product)
                {
                    if(isset($product->variants[0]))
                        $product->variant = $product->variants[0];
                    if(isset($product->images[0]))
                        $product->image = $product->images[0];
                } 
                
                // Передаем в шаблон
                $this->design->assign('products', $products); 
            } 
             
             
            //Блог
            if(count($posts_ids) > 0) {
                $posts = array();
                foreach($this->blog->get_posts(array('id'=>$posts_ids)) as $p)
                    $posts[$p->id] = $p;
                
                // Передаем в шаблон    
                $this->design->assign('posts', $posts);  
            }
            
        }
        
        // Устанавливаем мета-теги в зависимости от запроса
        if($this->page)
        {
            $this->design->assign('meta_title', $this->page->meta_title);
            $this->design->assign('meta_keywords', $this->page->meta_keywords);
            $this->design->assign('meta_description', $this->page->meta_description);
        }
        elseif(isset($keyword))
        {
            
             if($tagsValue)
             {
                 $keyword = $tagsValue->name;
                 
             }
             
             
              if($tagsValue->meta_title !== '')
             {
                 
                 $this->design->assign('meta_title', $tagsValue->meta_title);
                 $this->design->assign('meta_title_d', $tagsValue->meta_title);
                
             }
             else
             {
                 $this->design->assign('meta_title', $keyword);
             }
             
              if($tagsValue->meta_keywords !== '')
             {
                 
                 
                 $this->design->assign('meta_keywords', $tagsValue->meta_keywords);
             }
            
            
                if($tagsValue->meta_d !== '')
             {
                 $this->design->assign('meta_description', $tagsValue->meta_d);
                 
             }
			    if($tagsValue->text !== '')
             {
                 $this->design->assign('text', $tagsValue->text);
                 
             }
            
            
            
        }
        
        return $this->design->fetch('tags.tpl');
	}

}