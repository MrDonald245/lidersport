  </div>
  <footer class="footer">
    <div class="footer__wrapper wrapper">
      <div class="footer__info">
        <p class="footer__copyright">© 2015, Copyright Lidersport.<br> «Лидерспорт» — спортивные товары</p>
        <img src="/design/{$settings->theme}/images/pay-cards.png" alt="cards" class="footer__cards">
      </div>

      <nav class="footer__navigation">
        <a href="/o-nas" class="footer__item">О нас</a>
        <a href="" class="footer__item">Каталог товаров</a>
        <a href="/dostavka-i-oplata" class="footer__item">Доставка и оплата</a>
        <a href="/stock" class="footer__item">Новости и Акции</a>
        <a href="/confid" class="footer__item">Политика конфиденциальности</a>
      </nav>

      <nav class="footer__navigation">
        <a href="/contact" class="footer__item">Адреса магазинов</a>
        <a href="/contact" class="footer__item">Точки выдачи товара</a>
        <a href="/" data-popup-button="enter" class="footer__item">Вход в личный кабинет</a>
<!--        <a href="/" data-popup-button="register" class="footer__item">Получить скидку 10%</a> -->
      </nav>

      <div class="footer__contacts">
        <a href="tel:+78652990059" class="footer__phone">8 (8652) 99-00-59</a>
        <span class="footer__time">ждем звонки с 9:00 до 20:00</span>
      </div>
    </div>
  </footer>

  {include file='popups.tpl'}


  <script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min1.js"></script>
  <script type="text/javascript" src="/js/ctrlnavigate.js"></script>
  <script type="text/javascript" src="/js/jquery.query-object.js"></script>
  <script type="text/javascript" src="/design/{$settings->theme}/js/javascript.js"></script>
  <script type="text/javascript" src="/design/{$settings->theme}/js/jquery.cookie.js"></script>
  <script type="text/javascript" src="/slider/OwlCarousel2-2.2.1/dist/owl.carousel.min.js"></script>
  <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
  <script src="/design/{$settings->theme}/js/jquery.rater.js"></script>
  <script src="/design/{$settings->theme}/libs/elevatezoom/jquery.elevateZoom-3.0.8.min.js"></script>
  <script src="/design/{$settings->theme}/libs/page-scroll-to-id/js/minified/jquery.malihu.PageScroll2id.min.js"></script>
  <script src="/design/{$settings->theme}/libs/raty/lib/jquery.raty.js"></script>
  <link rel="stylesheet" type="text/css" href="/design/{$settings->theme}/libs/raty/lib/jquery.raty.css">
  
  <script type="text/javascript" src="/design/{$settings->theme}/js/filter.js"></script>
  <script type="text/javascript" src="/design/{$settings->theme}/js/common.js"></script>

  <script type="text/javascript">
    /* start --- Выбор города по IP --- */

     ymaps.ready(function () {
         //console.log('enter');
    
         ymaps.geolocation.get({
             // Зададим способ определения геолокации
             // на основе ip пользователя.
             provider: 'yandex',
             // Включим автоматическое геокодирование результата.
             autoReverseGeocode: true
         }).then(function (result) {
             // Выведем результат геокодирования.
             var city = result.geoObjects.get(0).properties.get('metaDataProperty.GeocoderMetaData.Address.formatted');
    
    //        if(!$.cookie('city')) {
    //
                 $.cookie('city', city, { path: '/' });
    //             $('.header__town').html($.cookie('city'));
    //             //console.log('test222');
    //             if ($(window).width() > 768) {
    //                 //console.log('test');
    //                 $.arcticmodal('close');
    //                 $('.p-choice-city').arcticmodal();
    //                 $('.p-choice-city__title').html("Ваш город " + city + "?");
    //                 $('.p-choice-city__body').html("<input type='button' class='issue__send' id='city__yes' value='Да'/> <input type='button' class='issue__send' id='city__no' value='Выбрать'/>");
    //             }
    //         }
    //         else {
                 $('.header__town').html($.cookie('city'));
                 //console.log('else');
    //         }
    //
             $('.p-choice-city__body').on("click", "#city__yes", function () {
                 $.cookie('city', city, { path: '/' });
                 $('.header__town').html($.cookie('city'));
                 $.arcticmodal('close');
             });
    //
    //         // Событие #city__no в commom.js
    //
           });
       });

    /* end --- Выбор города по IP --- */
  </script>

  {literal}
    <!-- Begin LeadBack code
          <script>
              var _emv = _emv || [];
              _emv['campaign'] = '8b02706f81704fa4ccc8719a';

              (function() {
                  var em = document.createElement('script'); em.type = 'text/javascript'; em.async = true;
                  em.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'leadback.ru/js/leadback.js';
                  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(em, s);
              })();
          </script>
      End LeadBack code -->
            

    <script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = 'https://vk.com/rtrg?p=VK-RTRG-157703-aOKt3';</script>

    <!-- Yandex.Metrika counter -->

    <script type="text/javascript" >

        (function (d, w, c) {

            (w[c] = w[c] || []).push(function() {

                try {

                    w.yaCounter36200345 = new Ya.Metrika({

                        id:36200345,

                        clickmap:true,

                        trackLinks:true,

                        accurateTrackBounce:true,

                        webvisor:true

                    });

                } catch(e) { }

            });

            var n = d.getElementsByTagName("script")[0],

                s = d.createElement("script"),

                f = function () { n.parentNode.insertBefore(s, n); };

            s.type = "text/javascript";

            s.async = true;

            s.src = "https://mc.yandex.ru/metrika/watch.js";

            if (w.opera == "[object Opera]") {

                d.addEventListener("DOMContentLoaded", f, false);

            } else { f(); }

        })(document, window, "yandex_metrika_callbacks");

    </script>

    <noscript><div><img src="https://mc.yandex.ru/watch/36200345" style="position:absolute; left:-9999px;" alt="" /></div></noscript>

    <!-- /Yandex.Metrika counter -->

    <!-- Global site tag (gtag.js) - Google Analytics -->

    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-102377179-3"></script>

    <script>

        window.dataLayer = window.dataLayer || [];

        function gtag(){dataLayer.push(arguments);}

        gtag('js', new Date());

        gtag('config', 'UA-102377179-3');

    </script>

    <!-- /Global site tag (gtag.js) - Google Analytics -->
    <!-- BEGIN JIVOSITE CODE
<script type='text/javascript'>
(function(){ var widget_id = 'jO5RRAbhMw';var d=document;var w=window;function l(){
var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=='complete'){l();}else{if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();</script>
  END JIVOSITE CODE -->

  {/literal}



</body>
</html>