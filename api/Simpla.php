<?php

/**
 * Основной класс Simpla для доступа к API Simpla
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */

/**
 * @property Config     $config
 * @property Request    $request
 * @property Database   $db
 * @property Settings   $settings
 * @property Design     $design
 * @property Products   $products
 * @property Variants   $variants
 * @property Categories $categories
 * @property Brands     $brands
 * @property Features   $features
 * @property Money      $money
 * @property Pages      $pages
 * @property Blog       $blog
 * @property Cart       $cart
 * @property Image      $image
 * @property Delivery   $delivery
 * @property Payment    $payment
 * @property Orders     $orders
 * @property Users      $users
 * @property Coupons    $coupons
 * @property Comments   $comments
 * @property Feedbacks  $feedbacks
 * @property Notify     $notify
 * @property Colors     $colors
 * @property Slides     $slides
 * @property Tags       $tags
 */
class Simpla
{
    private $classes = array(
        'config'     => 'Config',
        'request'    => 'Request',
        'db'         => 'Database',
        'settings'   => 'Settings',
        'design'     => 'Design',
        'products'   => 'Products',
        'variants'   => 'Variants',
        'categories' => 'Categories',
        'brands'     => 'Brands',
        'features'   => 'Features',
        'money'      => 'Money',
        'pages'      => 'Pages',
        'blog'       => 'Blog',
        'news'       => 'News',
        'cart'       => 'Cart',
        'image'      => 'Image',
        'delivery'   => 'Delivery',
        'payment'    => 'Payment',
        'orders'     => 'Orders',
        'users'      => 'Users',
        'coupons'    => 'Coupons',
        'comments'   => 'Comments',
        'feedbacks'  => 'Feedbacks',
        'notify'     => 'Notify',
        'colors'     => 'Colors',
        'slides'     => 'Slides',
        'tags'       => 'Tags',
    );
	
	// Созданные объекты
	private static $objects = array();
	
	/**
	 * Конструктор оставим пустым, но определим его на случай обращения parent::__construct() в классах API
	 */
	public function __construct()
	{
	}

	/**
	 * Магический метод, создает нужный объект API
	 */
	public function __get($name)
	{
		// Если такой объект уже существует, возвращаем его
		if(isset(self::$objects[$name]))
		{
			return(self::$objects[$name]);
		}
		
		// Если запрошенного API не существует - ошибка
		if(!array_key_exists($name, $this->classes))
		{
			return null;
		}
		
		// Определяем имя нужного класса
		$class = $this->classes[$name];
		
		// Подключаем его
		//include_once('api/'.$class.'.php');
		include_once(dirname(__FILE__).'/'.$class.'.php');

		// Сохраняем для будущих обращений к нему
		self::$objects[$name] = new $class();
		
		// Возвращаем созданный объект
		return self::$objects[$name];
	}
}