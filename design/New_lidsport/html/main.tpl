{include file='slider/slider.tpl'}

<div class="mainBanners">
 <a href="/samokaty-dlya-detej"><img src="/files/slides/mini_1.jpg"></a>
 <a href="/velosipedy"><img src="/files/slides/mini_2.jpg"></a>
 <a href="https://app.halvacard.ru/order/?utm_medium=Partner&utm_source=%7bNAME%7d&utm_campaign=halva"><img src="/files/slides/mini_halva.jpg"></a>
</div>

{get_featured_products var=featured_products}
{if $featured_products}
  <div class="sale">
    <div class="sale__wrapper wrapper">
      <h2 class="sale__title">Хиты продаж</h2>
      <div class="product-list">
        {foreach $featured_products as $product}
				  <div class="product-list__item product-list__item--thin">
					  {include file='_tiny-product.tpl'}
				  </div>
        {/foreach}
      </div>
    </div>
  </div>
{/if}

<div class="about">
  <img src="/design/{$settings->theme}/images/woman.png" alt="woman" class="about__bg">
  <div class="about__wrapper wrapper">
    <h1 style="position:absolute;top:75px;left:72px;color:rgb(255,255,255);font-size:22px;width:580px;height:36px;/*background-color:rgb(225,216,232);*/
    text-align:center;padding-top:5px;/*opacity:0.8;border-radius:3px;*/text-transform:none !important;">Лидерспорт - интернет-магазин спортивных товаров</h1>
    <style>
      @media screen and (max-width: 1225px) {
        h1 {
          top: 50px !important;
          left: 25px !important;
          max-width: 500px !important;
        }
      }
      @media screen and (max-width:768px) {
        h1 {
          top: 20px !important;
          left: 25px !important;
          max-width: 500px !important;
        }
      }
    </style>
    <p class="about__text">Лидерспорт – это сеть спортивных магазинов для всей семьи! Все для спорта и активного отдыха вы найдете в наших магазинах, вне зависимости от того являетесь ли вы профессиональным спортсменом или только начинаете вести здоровый образ жизни. Наши консультанты помогут найти оптимальное решение каждому покупателю, исходя из его потребностей и уровня спортивной подготовки.</p>
    <p class="about__text">Лидерспорт - это молодая динамично развивающая компания. Мы хотим стать ближе к каждому из вас! На сегодняшний день наши магазины представлены во все крупных районах города.</p>
    <p class="about__text">Мы тщательно следим за нашим ассортиментом и ценовой политикой, отбирая только лучших поставщиков, предоставляя вам качественные товары. Являемся дилерами многих российских производителей.</p>
    <div class="about__advantages">
      <div class="about__price">
        <img src="/design/{$settings->theme}/images/ico/star-yellow.svg" alt="star-ico" class="about__ico">
        <span>Лучшие цены</span>
      </div>
      <div class="about__quality">
        <img src="/design/{$settings->theme}/images/ico/chek_circle.svg" alt="chek-ico" class="about__ico">
        <span>Качественные товары</span>
      </div>
    </div>
  </div>
</div>
<div class="advantages">
  <ul class="advantages__list">
    <li class="advantages__item">
      <img src="/design/{$settings->theme}/images/ico/number.png" alt="" class="advantages__ico">
      <p class="advantages__description">Более 5 000 наименований<br> товара.</p>
    </li>
    <li class="advantages__item">
      <img src="/design/{$settings->theme}/images/ico/delivery.png" alt="" class="advantages__ico">
      <p class="advantages__description">Доставка товара в любую точку<br> России и стран СНГ.</p>
    </li>
    <li class="advantages__item">
      <img src="/design/{$settings->theme}/images/ico/pay.png" alt="" class="advantages__ico">
      <p class="advantages__description">Наличные и безналичные расчеты!<br> Удобные системы оплаты.</p>
    </li>
    <li class="advantages__item">
      <img src="/design/{$settings->theme}/images/ico/dispatch.png" alt="" class="advantages__ico">
      <p class="advantages__description">Отправка товара в день<br> оформления заказа!</p>
    </li>
  </ul>
</div>