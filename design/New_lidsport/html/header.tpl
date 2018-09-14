<!DOCTYPE html>
{*
  Общий вид страницы
  Этот шаблон отвечает за общий вид страниц без центрального блока.
*}
<html>
<head>
  <title>{if ($module=='MainView' or $module=='NewsView' or $module=='OrderView' or $module=='TagsView'  or $module=='RegisterView' or $module=='StockView' or $module=='UserView' or $module=='CartView' or $module=='FeedbackView' or $module=='BlogView' or ($module=='PageView' and !$product and !$products)) AND $meta_title_d|escape !== ''}{$meta_title|escape}{else} Купить {mb_strtolower($meta_title)|escape} с доставкой и недорого в интернет магазине Лидерспорт Ставрополь: цены, отзывы, акции, скидки{/if}</title>

  <meta name='yandex-verification' content='73e703aa7eb82fe0' />

  {* Метатеги *}
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="description" content="{if ($module=='MainView' or $module=='NewsView' or $module=='OrderView' or $module=='RegisterView' or $module=='StockView' or $module=='UserView' or $module=='CartView' or $module=='FeedbackView' or $module=='BlogView' OR ($module=='TagsView' AND $meta_title !== '')   or ($module=='PageView' and !$product and !$products)) AND $meta_description|escape !== ''}{$meta_description|escape}{else}Купить {mb_strtolower($meta_title)|escape} недорого можно в магазинах Лидерспорт в Ставрополе или заказать с доставкой по России в нашем интернет магазине. Переходите на сайт, чтобы узнать цены, посмотреть скидки, акции и распродажи на {mb_strtolower($meta_title)|escape}{/if}" />

  <meta name="keywords" content="{$meta_title|escape}, {$meta_keywords|escape}" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" >
  <meta name="google-site-verification" content="N7c33DNkhR0TwD7NnD4f9AW_crlAWVtaTWBYt7uGLNA" />

  <link href="/favicon.ico" rel="icon" type="image/x-icon"/>

	{* JQuery *}
  <script type="text/javascript" src="/design/{$settings->theme}/js/libs.min.js"></script>
  <script src="/design/{$settings->theme}/js/jquery.cookie.js" type="text/javascript"></script>

  {* CSS *}
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/libs.min.css">
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/main.css">
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/component-category-bar.css">
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/component-category-tags.css">
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/component-filter.css">
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/component-pagination.css">
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/component-product-list.css">
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/css/ui-sort.css">

  <link rel="stylesheet" type="text/css" href="/slider/OwlCarousel2-2.2.1/dist/assets/owl.carousel.min.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="/slider/OwlCarousel2-2.2.1/dist/assets/owl.theme.green.min.css" media="screen" />

</head>
<body>
  <div class="container">
    <header class="header">
      <div class="header__info">
        <div class="header__wrapper wrapper">
          <div class="header__city"><span class="header__town icon icon-map">Определение</span></div>
          <div class="header-centered">
            <a href="/contact" class="header__delivery">Магазины/пункты выдачи</a>
            <a href="/dostavka-i-oplata" class="header__delivery">Доставка и оплата</a>
            <a href="/stock" class="header__delivery">Новости и акции</a>
          </div>
          <div class="header__sign">
              {if $user}
                  <a href="/user/logout" class="standard-header-text"><span class="pointered">
                  Выйти
                    </span></a>/<a href="/user" class="standard-header-text"><span class="pointered">
                  {$user->name}
                  </span></a>
              {else}
                <span class="pointered" data-popup-button="enter">
                  Вход
                </span>/<span class="pointered" data-popup-button="register">
                  Регистрация
                </span>
              {/if}
          </div>
        </div>
      </div>

      <div class="header__middle">
        <div class="header__wrapper wrapper">
          <a href="/" class="header__logo">
            <img src="/design/{$settings->theme}/images/logo.png" alt="logo">
          </a>
    			<form action="/products" id="search-form" class="header__search search mobile-form">
            <input type="text" class="search__input" name="keyword" placeholder="Поиск товаров" autocomplete="off">
            <input type="submit" id="search-button" class="search-button_mobile" value="Найти">
          </form>
          <form action="/products" id="search-form2" class="header__search search desktop-form">
            <input type="text" class="search__input___styled effect-1 search__input2" name="keyword" placeholder="Поиск товаров" autocomplete="off">
            <span class="focus-border"></span>
            <button type="submit" id="search-button2" class="search__button___ico">
              <img src="/design/New_lidsport/images/new_header/icon-search.png" alt="">
            </button>
          </form>
          <div class="header-phone-bold">
            {*<span>+7 (8652) 22-15-77</span>*}
            <span>8 (8652) 99-00-59</span>
          </div>
          {include file='cart_informer.tpl'}
        </div>
      </div>

      <!-- PC menu -->
      <div class="header__navigation">
        <div class="header__wrapper wrapper">
         
          <div class="header__button">
            <span class="header__button-name">Каталог товаров</span>
            <nav class="header__menu menu">
             
              <!-- Displaying menu items -->
              {foreach $categories as $cat}
                {if $cat->visible && $cat->name != "Распродажа"}
                  {if $cat->subcategories}
                    <div class="menu__item">
                      <a href="/{$cat->url}" class="menu__link">
                        <span class="menu__link__name">{$cat->name|escape}</span>
                        <span class="menu__link__style"></span>
                      </a>
                      <div class="menu__navigation">
                        <div class="menu__navigation__body">
                          {foreach $cat->subcategories as $subcat}
                            {if $subcat->visible}
                              <div class="menu__navigation__item">
                                <a href="/{$subcat->url}" class="menu__subcat">
                                  <span class="">{$subcat->name|escape}</span>
                                </a>
                                {foreach $subcat->subcategories as $subcat_subcat}
                                  {if $subcat_subcat->visible}
                                    <a href="/{$subcat_subcat->url}" class="menu__subcat__subcat">
                                      <span class="">{$subcat_subcat->name|escape}</span>
                                    </a>
                                  {/if}
                                {/foreach}
                              </div>
                            {/if}
                          {/foreach}
                        </div>
                      </div>
                    </div>

                  {else}
                    <div class="menu__item">
                      <p style="display: none">{$cat->name|escape}</p>
                      <a href="/{$cat->url}" class="menu__link">
                        <span class="menu__link__name">{$cat->name|escape}</span>
                      </a>
                    </div>
                  {/if}

                {/if}
              {/foreach}

            </nav>
          </div>
          <!-- End PC menu -->

          <div class="search-ico"></div>
          <a href="/edinoborstva" class="header__item">ЕДИНОБОРСТВА</a>
          <a href="/gimnastika-i-tantsy" class="header__item">ГИМНАСТИКА И ТАНЦЫ</a>
          <a href="/dlya-doma" class="header__item">ДЕТСКИЕ СПОРТКОМПЛЕКСЫ</a>
          <a href="/fitnes-i-atletika" class="header__item">ФИТНЕС И АТЛЕТИКА</a>
          <a href="/sale?page=all" class="header__item header__item--icon header__item--shares">Распродажа</a>
        </div>
      </div>

      <div class="header__overlay"></div>

      <!-- Desctop menu -->
      <nav class="m-menu">
      	{* <h2 style="display:none">Каталог товаров</h2> *}
        <img src="/design/{$settings->theme}/images/ico/close_mobile_menu.svg" alt="close" class="m-menu__close">
        <!-- Displaying desctop menu items -->

        {foreach $categories as $cat}
          {if $cat->visible  && $cat->name != "Распродажа"}

            <div class="m-menu__item">

              <div class="m-menu__caption">
                <a href="/{$cat->url}" class="m-menu__link">{$cat->name|escape}</a>
                <img src="/design/{$settings->theme}/images/ico/arrow_menu.svg" alt="arrow" class="m-menu__arrow">
              </div>

              <ul class="m-menu__navigation">
                {foreach $cat->subcategories as $subcat}
                  {if $subcat->visible}
                    <li class="m-menu__item">
                      <a href="/{$subcat->url}" class="m-menu__link">{$subcat->name|escape}</a>
                      {foreach $subcat->subcategories as $subcat_subcat}
                        {if $subcat_subcat->visible}
                          <a href="/{$subcat_subcat->url}" class="m-menu__link">
                            - {$subcat_subcat->name|escape}
                          </a>
                        {/if}
                      {/foreach}
                    </li>
                  {/if}
                {/foreach}
              </ul>
              
            </div>
            
          {/if}
        {/foreach}

        <!-- End displaying desctop menu items -->
      </nav>
      <!-- End desctop menu -->
      {* <meta name="description" content="{$meta_title|escape}, {$meta_description|escape}" />  старый вариант *}
    </header>