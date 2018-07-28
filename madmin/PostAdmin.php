<?PHP

require_once('api/Simpla.php');

class PostAdmin extends Simpla
{
	private	$allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
	public function fetch()
	{
		if($this->request->method('post'))
		{
			$post->id = $this->request->post('id', 'integer');
			$post->name = $this->request->post('name');
			$post->date = date('Y-m-d', strtotime($this->request->post('date')));
			
			$post->visible = $this->request->post('visible', 'boolean');

			$post->url = $this->request->post('url', 'string');
			$post->meta_title = $this->request->post('meta_title');
			$post->meta_keywords = $this->request->post('meta_keywords');
			$post->meta_description = $this->request->post('meta_description');
			
			$post->annotation = $this->request->post('annotation');
			$post->text = $this->request->post('body');
			//$post->video = "";//$this->request->post('video');
			//$post->image = "";$this->request->post('exist_image');
 			// Не допустить одинаковые URL разделов.
			if(($a = $this->blog->get_post($post->url)) && $a->id!=$post->id)
			{			
				$this->design->assign('message_error', 'url_exists');
			}
			else
			{
				if(empty($post->id))
				{
	  				$post->id = $this->blog->add_post($post);
	  				$post = $this->blog->get_post($post->id);
					$this->design->assign('message_success', 'added');
	  			}
  	    		else
  	    		{
  	    			$this->blog->update_post($post->id, $post);
  	    			$post = $this->blog->get_post($post->id);
					$this->design->assign('message_success', 'updated');
  	    		}	

   	    		
			}
			// Удаление изображения
    		if($this->request->post('delete_image'))
    		{
    			$this->blog->delete_image($post->id);
    		}
			// Загрузка изображения
    		$image = $this->request->files('image'); 
    		if(!empty($image['name']) && in_array(strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)), $this->allowed_image_extentions))
  	    	{
    			$this->blog->delete_image($post->id);
    			move_uploaded_file($image['tmp_name'], $this->config->root_dir.$this->config->stock_images_dir.$image['name']);
    			$this->blog->update_post($post->id, array('image'=>$image['name']));
    		}
    		$post = $this->blog->get_post(intval($post->id));
  	    		
		}
		else
		{
			$post->id = $this->request->get('id', 'integer');
			$post = $this->blog->get_post(intval($post->id));
		}

		if(empty($post->date))
			$post->date = date($this->settings->date_format, time());
 		
		$this->design->assign('post', $post);
		
		
 	  	return $this->design->fetch('post.tpl');
	}
}