<?PHP
//error_reporting(E_ALL ^ E_NOTICE);
chdir('../');
include('api/Simpla.php');

setlocale(LC_ALL, "ru_RU.UTF-8");


function process_tag($name,$cat)
{
    
    $simpla = new Simpla();
    
    $tags = explode("#",$name);
    
    
    
    
    $names = explode("/",$cat);
    $all = count($names);
    
    $name = trim($names[$all - 1]);
    
    
        

        if(!empty($name) and $name!="Категория")
        {
            
            
            
        
    
    
    
    
    $simpla->db->query("SELECT id FROM __categories WHERE  name=? LIMIT 1", $name);
            
    $catId = $simpla->db->result("id");
    
    if($catId > 0)
    {
        
    
    
    foreach ($tags as $value) {
                    $query = $simpla->db->placehold("INSERT IGNORE INTO __tags SET type=?, object_id=?, value=?", 'categori', intval($catId), $value);
                    $simpla->db->query($query);
                    
                    if(!$simpla->tags->get_tag((string)$value,'sdsd'))
                                {
                                    $tag = new stdClass;
                                    $tag->name = $value;

                                    $simpla->tags->add_tag($tag);
                                    
                                }
                    }
    
    }
    
    
}

                                

}



function process_category($name)
{
    $simpla = new Simpla();
    // echo "<br>-".$name;
    // Поле "категория" может состоять из нескольких имен, разделенных subcategory_delimiter-ом
    // Только неэкранированный subcategory_delimiter может разделять категории

    /*$delimeter = $subcategory_delimiter;
    $regex = "/\\DELIMETER((?:[^\\\\\DELIMETER]|\\\\.)*)/";
    $regex = str_replace('DELIMETER', $delimeter, $regex);
    $names = preg_split($regex, $name, 0, PREG_SPLIT_DELIM_CAPTURE);*/

    $names = explode("/",$name);

    $result_category_id = null;
    $current_parent = 0;
    $all = count($names);
    $i=0;
    for($ii=0;$ii<$all;$ii++)
    {
        $name = trim($names[$ii]);
        $title = title($name);

        if(!empty($name) and $name!="Категория")
        {
            //echo "<br>---".$name;
            $simpla->db->query("SELECT id FROM __categories WHERE parent_id=? AND name=? LIMIT 1", $current_parent, $name);
            // $simpla->db->query("SELECT id FROM __categories WHERE name=? LIMIT 1", $name);
            $cat = $simpla->db->result("id");


            if($cat>0)
            {
                $result_category_id = $cat;
                $current_parent = $result_category_id;
            }
            else
            {
                $url = translit($name);

                $query = $simpla->db->query("INSERT INTO __categories(name, parent_id,content_title,meta_title,url) VALUES(?,?,?,?,?)",$name,$current_parent,$title,$title,$url);
                $result_category_id = $simpla->db->insert_id();
                $current_parent = $result_category_id;
            }

            //echo "<br> $i / $all category = ".$result_category_id;
        }

    }
    echo $result_category_id;
    return $result_category_id;
}

function translit($text)
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

function title($name)
{
    $des = array(
        "Футбол" => "Футбольная экипировка",
        "Баскетбол" => "Баскетбольная экипировка",
        "Волейбол" => "Волейбольная экипировка",
        "Гандбол" => "Экипировка для гандбола",
        "Акссесуары" => "Насосы для мячей, конусы спортивные",
        "Бадминтон и большой теннис" => "Экипировка для бадминтона и большого тенниса",
        "Бейсбол" => "Бейбольные биты и мячи",
        "Акссесуары для пляжа" => "Все для пляжа",
        "Насосы Intex, Bestway" => "Насосы для бассейнов",
        "Горные" => "Горные велосипеды",
        "Женские" => "Женские велосипеды",
        "Городские и дорожные" => "Городские и дорожные велосипеды",
        "Детские и подростковые" => "Детские велосипеды",
        "Подростковые" => "Подростковые велосипеды",
        "BMX" => "BMX (БМХ)",
        "Велоакссесуары" => "Аксессуары для велосипедов",
        "Инвентарь для гимнастики" => "Товары для гимнастики и танцев",
        "Одежда для гимнастики" => "Одежда для гимнастики и танцев",
        "Мини-лыжи" => "Детские лыжи",
        "Плавки для плавания" => "Плавки",
        "Купальники" => "Купальник",
        "Аксессуары для плавания" => "Доски для плавания, колобашки, беруши",
        "Обувь для бассейна" => "Сланцы и шлепанцы",
        "Гамаки и туристическая мебель" => "Туристическая мебель",
        "Надувная продукция" => "Надувные кровати и подушки",
        "Мячи для фитнеса" => "Гимнастические мячи для фитнеса",
        "Для спортивных залов" => "Оборудование для спортивных залов ",
        "Напитки" => "Напитки спорт",
        "Повышение тестостерона" => "Комплексы для повышения тестостерона",
        "Снижение веса" => "Препараты для снижения веса",
        "Спецпрепараты" => "Специальные препараты",
        "Суставы и связки" => "Для суставов и связок",
        "Углеводы" => "Углеводы для похудения и набора массы",
        "Оборудование для футбола" => "Футбольное оборудование",
        "Атрибутика болельщика" => "Футбольная атрибутика",
        "Сетки" => "Сетки для настольного тенниса",
        "Столы теннисные" => "Столы для настольного тенниса",
        "Ракетки" => "Ракетки для настольного тенниса",
        "3-х колесные" => "Трехколесные велосипеды",
        "2-х и 4-х колесные" => "Двухколесные и четырехколесные велосипеды",
        "Фляги и флягодержатели" => "Фляги и флягодержатели для велосипеда",
        "Фонари велосипедные" => "Фонари для велосипеда",
        "Велосумки и корзины" => "Велосумки и корзины для велосипедов",
        "Звонки" => "Звонки велосипедные",
        "Камеры и покрышки" => "Покрышки и камеры для велосипедов",
        "Грипсы и рога" => "Грипсы и рога для велосипеда",
        "Инструмент" => "Инструмент для велосипеда",
        "Насосы" => "Насосы велосипедные",
        "Тормозные колодки" => "Колодки для велосипеда",
        "Педали" => "Педали для велосипеда",
        "Подножки" => "Подножка для велосипеда",
        "Седла" => "Седла для велосипеда",
        "Купальники" => "Купальники для гимнастики и танцев",
        "Юбки" => "Юбки для гимнастики и танцев",
        "Шлема" => "Шлема для единоборств",
        "Специальная защита" => "Защита для единоборств",
        "Обувь для бокса" => "Боксерки",
        "Обувь для борьбы" => "Борцовки",
        "Наборы для бокса" => "Детские наборы для бокса",
        "Эллиптические" => "Эллиптические тренажеры (эллипсоиды)",
        "Пояса для тяжелой атлетики" => "Пояса атлетические",
        "Сумки и рюкзаки" => "Сумки и рюкзаки спортивные",
        "Прочее" => "Магнезия, шейкеры, маски для тренировок",
        "Профессиональные" => "Профессиональные тренажеры",
        "Домашние" => "Тренажеры для дома",
        "Снаряжение" => "Тренажеры и манекены для отработки ударов",
        "Мешки и груши" => "Груши и мешки для бокса, манекены Германы",
        "Спортивные комплексы" => "Детские спортивные игровые комплексы ДСК",
        "Для дома" => "Детские спортивные комплексы ДСК для дома и в квартиру",
        "Для улицы" => "Детские площадки на дачу",
        "Песочницы и качели" => "Песочницы, качели, горки для улицы",
        "Батуты" => "Детские батуты",
        "Перчатки" => "Перчатки для тяжелой атлетики и фитнеса",
        "Коврики, кариматы и маты" => "Коврики, кариматы и маты для фитнеса",
        "6.5 дюймов" => "Гироскутеры SMART BALANCE 6.5",
        "8 дюймов" => "Гироскутеры SMART BALANCE 8",
        "10 дюймов" => "Гироскутеры SMART BALANCE 10",
        "10.5 дюймов" => "Гироскутеры SMART BALANCE 10.5",

    );
    foreach ($des as $s => $val) {
        if($name == $s) {
            $title = $val;
            return $title;
        }
    }
    return $name;

}

$list = array(
    "Активный отдых",
    "Активный отдых/Активные игры",
    "Активный отдых/Гироскутеры",
    "Активный отдых/Самокаты",
    "Активный отдых/Скейты",
    "Активный отдых/Ролики",
    "Активный отдых/Кузнечики",
    "Активный отдых/Защита и аксессуары",
    "Бассейны, пляж, аксессуары",
    "Бассейны, пляж, аксессуары/Акссесуары для пляжа",
    "Бассейны, пляж, аксессуары/Бассейны",
    "Бассейны, пляж, аксессуары/Насосы Intex, Bestway",
    "Бассейны, пляж, аксессуары/Трубки и маски",
    "Бассейны, пляж, аксессуары/Ласты",
    "Бассейны, пляж, аксессуары/Химия и акссесуары для бассейнов",
    "Велосипеды",
    "Велосипеды/Городские и дорожные",
    "Велосипеды/Горные",
    "Велосипеды/Женские",
    "Велосипеды/Детские и подростковые",
    "Велосипеды/Детские и подростковые/2-х и 4-х колесные",
    "Велосипеды/Детские и подростковые/3-х колесные",
    "Велосипеды/Детские и подростковые/Беговелы",
    "Велосипеды/Детские и подростковые/Бибикары",
    "Велосипеды/Детские и подростковые/Подростковые",
    "Велосипеды/BMX",
    "Велосипеды/Фэтбайки",
    "Велосипеды/Велоаксессуары",
    "Велосипеды/Велоаксессуары/Велокомпьютеры",
    "Велосипеды/Велоаксессуары/Велосумки и корзины",
    "Велосипеды/Велоаксессуары/Грипсы и рога",
    "Велосипеды/Велоаксессуары/Детские велокресла",
    "Велосипеды/Велоаксессуары/Замки велосипедные",
    "Велосипеды/Велоаксессуары/Звонки",
    "Велосипеды/Велоаксессуары/Инструмент",
    "Велосипеды/Велоаксессуары/Камеры и покрышки",
    "Велосипеды/Велоаксессуары/Насосы",
    "Велосипеды/Велоаксессуары/Педали",
    "Велосипеды/Велоаксессуары/Перчатки велосипедные",
    "Велосипеды/Велоаксессуары/Подножки",
    "Велосипеды/Велоаксессуары/Седла",
    "Велосипеды/Велоаксессуары/Тормозные колодки",
    "Велосипеды/Велоаксессуары/Фляги и флягодержатели",
    "Велосипеды/Велоаксессуары/Фонари велосипедные",
    "Гимнастика и танцы",
    "Гимнастика и танцы/Инвентарь для гимнастики",
    "Гимнастика и танцы/Обувь для гимнастики и танцев",
    "Гимнастика и танцы/Одежда для гимнастики",
    "Гимнастика и танцы/Одежда для гимнастики/Гетры и колготки",
    "Гимнастика и танцы/Одежда для гимнастики/Купальники",
    "Гимнастика и танцы/Одежда для гимнастики/Лосины и полулосины",
    "Гимнастика и танцы/Одежда для гимнастики/Майки и футболки",
    "Гимнастика и танцы/Одежда для гимнастики/Шорты",
    "Гимнастика и танцы/Одежда для гимнастики/Юбки",
    "Единоборства",
    "Единоборства/Защита для единоборств",
    "Единоборства/Защита для единоборств/Бинты боксёрские",
    "Единоборства/Защита для единоборств/Защита корпуса",
    "Единоборства/Защита для единоборств/Защита ног",
    "Единоборства/Защита для единоборств/Специальная защита",
    "Единоборства/Защита для единоборств/Шлема",
    "Единоборства/Обувь для единоборств",
    "Единоборства/Обувь для единоборств/Обувь для бокса",
    "Единоборства/Обувь для единоборств/Обувь для борьбы",
    "Единоборства/Обувь для единоборств/Обувь для тхеквандо, кикбоксинга, рукопашного боя, самбо",
    "Единоборства/Одежда для единоборств",
    "Единоборства/Одежда для единоборств/Кимоно",
    "Единоборства/Одежда для единоборств/Пояса для единоборств",
    "Единоборства/Одежда для единоборств/Форма боксерская",
    "Единоборства/Одежда для единоборств/Форма для борьбы",
    "Единоборства/Одежда для единоборств/Форма для кикбоксинга и тайского бокса",
    "Единоборства/Одежда для единоборств/Форма для смешанных единоборств",
    "Единоборства/Перчатки и накладки на руки",
    "Единоборства/Снаряжение",
    "Единоборства/Снаряжение/Лапы и макивары",
    "Единоборства/Снаряжение/Мешки и груши",
    "Единоборства/Снаряжение/Наборы для бокса",
    "Зимние товары",
    "Зимние товары/Бенгальские огни",
    "Зимние товары/Ёлки искусственные",
    "Зимние товары/Клюшки и шайбы",
    "Зимние товары/Коньки",
    "Зимние товары/Мини-лыжи",
    "Зимние товары/Санки и ледянки",
    "Зимние товары/Снегокаты",
    "Зимние товары/Термобелье",
    "Оборудование",
    "Оборудование/Для спортивных залов",
    "Оборудование/Сетки заградительные",
    "Оборудование/Уличное оборудование",
    "Оборудование/Уличные тренажеры",
    "Оборудование/Для детских садов",
    "Плавание",
    "Плавание/Аксессуары для плавания",
    "Плавание/Купальники для плавания",
    "Плавание/Трубки и маски",
    "Плавание/Ласты",
    "Плавание/Обувь для бассейна",
    "Плавание/Очки для плавания",
    "Плавание/Плавки для плавания",
    "Плавание/Шапочки для плавания",
    "Спортивное питание",
    "Спортивное питание/Акссесуары для атлетики",
    "Спортивное питание/Аминокислоты",
    "Спортивное питание/Батончики",
    "Спортивное питание/Витамины и минералы",
    "Спортивное питание/Гейнеры",
    "Спортивное питание/Креатин",
    "Спортивное питание/Напитки",
    "Спортивное питание/Повышение тестостерона",
    "Спортивное питание/Протеин",
    "Спортивное питание/Снижение веса",
    "Спортивное питание/Спецпрепараты",
    "Спортивное питание/Суставы и связки",
    "Спортивное питание/Углеводы",
    "Спортивное питание/Энергетики",
    "Спортивные игры",
    "Спортивные игры/Футбол",
    "Спортивные игры/Футбол/Атрибутика болельщика",
    "Спортивные игры/Футбол/Бутсы",
    "Спортивные игры/Футбол/Защита для футбола",
    "Спортивные игры/Футбол/Мячи футбольные",
    "Спортивные игры/Футбол/Оборудование для футбола",
    "Спортивные игры/Футбол/Форма футбольная",
    "Спортивные игры/Футбол/Гетры футбольные",
    "Спортивные игры/Баскетбол",
    "Спортивные игры/Баскетбол/Мячи баскетбольные",
    "Спортивные игры/Баскетбол/Оборудование баскетбольное",
    "Спортивные игры/Волейбол",
    "Спортивные игры/Волейбол/Защита волейбольная",
    "Спортивные игры/Волейбол/Мячи волейбольные",
    "Спортивные игры/Волейбол/Оборудование волейбольное",
    "Спортивные игры/Гандбол",
    "Спортивные игры/Бадминтон и большой теннис",
    "Спортивные игры/Настольный теннис",
    "Спортивные игры/Настольный теннис/Мячи для настольного тенниса",
    "Спортивные игры/Настольный теннис/Ракетки",
    "Спортивные игры/Настольный теннис/Сетки",
    "Спортивные игры/Настольный теннис/Столы теннисные",
    "Спортивные игры/Бейсбол",
    "Спортивные игры/Бильярд",
    "Спортивные игры/Дартс",
    "Спортивные игры/Активные игры",
    "Спортивные игры/Классические настольные",
    "Спортивные игры/Судейская атрибутика",
    "Спортивные игры/Спиннеры",
    "Спортивные игры/Акссесуары",
    "Спортивные комплексы и батуты",
    "Спортивные комплексы и батуты/Спортивные комплексы",
    "Спортивные комплексы и батуты/Спортивные комплексы/Для дома",
    "Спортивные комплексы и батуты/Спортивные комплексы/Для улицы",
    "Спортивные комплексы и батуты/Спортивные комплексы/Песочницы и качели",
    "Спортивные комплексы и батуты/Батуты",
    "Спортивные комплексы и батуты/Маты гимнастические",
    "Спортивные комплексы и батуты/Акссесуары к детским комплексам",
    "Сувенирная продукция",
    "Тренажеры",
    "Тренажеры/Кардиотренажеры",
    "Тренажеры/Кардиотренажеры/Велотренажеры",
    "Тренажеры/Кардиотренажеры/Степперы",
    "Тренажеры/Кардиотренажеры/Эллиптические",
    "Тренажеры/Силовые тренажеры",
    "Тренажеры/Силовые тренажеры/Домашние",
    "Тренажеры/Силовые тренажеры/Профессиональные",
    "Тренажеры/Уличные тренажеры",
    "Тренажеры/Массажеры электрические",
    "Тренажеры/Запчасти для тренажеров",
    "Туризм",
    "Туризм/Гамаки и туристическая мебель",
    "Туризм/Инвентарь для туризма и отдыха на природе",
    "Туризм/Компасы",
    "Туризм/Надувная продукция",
    "Туризм/Палки для скандинавской ходьбы",
    "Туризм/Фонари",
    "Фитнес и атлетика",
    "Фитнес и атлетика/Фитнес",
    "Фитнес и атлетика/Фитнес/Гантели",
    "Фитнес и атлетика/Фитнес/Мячи для фитнеса",
    "Фитнес и атлетика/Фитнес/Коврики, кариматы и маты",
    "Фитнес и атлетика/Фитнес/Обручи",
    "Фитнес и атлетика/Фитнес/Ролики для пресса",
    "Фитнес и атлетика/Фитнес/Скакалки",
    "Фитнес и атлетика/Фитнес/Степплатформы, бодибары и твистеры",
    "Фитнес и атлетика/Фитнес/Эспандеры",
    "Фитнес и атлетика/Фитнес/Утяжелители",
    "Фитнес и атлетика/Тяжелая атлетика",
    "Фитнес и атлетика/Тяжелая атлетика/Гантели",
    "Фитнес и атлетика/Тяжелая атлетика/Гири",
    "Фитнес и атлетика/Тяжелая атлетика/Грифы",
    "Фитнес и атлетика/Тяжелая атлетика/Диски",
    "Фитнес и атлетика/Тяжелая атлетика/Пояса для тяжелой атлетики",
    "Фитнес и атлетика/Тяжелая атлетика/Утяжелители",
    "Фитнес и атлетика/Тяжелая атлетика/Бандажи",
    "Фитнес и атлетика/Тяжелая атлетика/Бинты",
    "Фитнес и атлетика/Легкая атлетика",
    "Фитнес и атлетика/Турники, брусья, упоры для отжимания",
    "Фитнес и атлетика/Турники, брусья, упоры для отжимания/Турники",
    "Фитнес и атлетика/Турники, брусья, упоры для отжимания/Упоры для отжимания",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Весы",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Массажёры",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Одежда для похудения",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Одежда спортивная",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Перчатки",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Повязки и напульсники",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Пояса для похудения",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Сумки и рюкзаки",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Шейкеры и бутылочки для питья",
    "Фитнес и атлетика/Акссесуары для фитнеса и атлетики/Прочее",
    "Активный отдых/Гироскутеры/6.5 дюймов",
    "Активный отдых/Гироскутеры/8 дюймов",
    "Активный отдых/Гироскутеры/10 дюймов",
    "Активный отдых/Гироскутеры/10.5 дюймов",
    "Активный отдых/Самокаты/Самокаты для детей",
    "Активный отдых/Самокаты/Самокаты для взрослых",
    "Активный отдых/Самокаты/Трюковые самокаты",
    
);



foreach($list as $cat) {
    process_category($cat);
    echo "<br/>";
}


$tags = array(
"Активный отдых/Самокаты"=>'#трехколесные самокаты#двухколесные самокаты#самокаты складные#самокаты для девочек#самокаты для мальчиков#самокаты городские#недорогие самокаты#самокаты со светящимися колесами#самокаты от года#самокаты от 2 лет#самокаты от 3 лет#самокаты от 4 лет#самокаты от 5 лет',
"Активный отдых/Самокаты/Самокаты для детей"=>'#трехколесные самокаты#двухколесные самокаты#самокаты складные#самокаты для девочек#самокаты для мальчиков#недорогие самокаты#самокаты со светящимися колесами#самокаты от года#самокаты от 2 лет#самокаты от 3 лет#самокаты от 4 лет',
"Активный отдых/Самокаты/Самокаты для взрослых"=>'#самокаты городские#двухколесные самокаты#самокаты от 5 лет',
"Активный отдых/Самокаты/Трюковые самокаты"=>'#самокаты городские#самокаты от 5 лет',
"Активный отдых/Гироскутеры"=>'#гироскутеры недорогие#гироскутеры для детей#гироскутеры с приложением#гироскутеры красные#гироскутеры для девочек#гироскутеры SUV#гироскутеры Premium',
"Активный отдых/Гироскутеры/6.5 дюймов"=>'#гироскутеры недорогие#гироскутеры для детей#гироскутеры с приложением#гироскутеры красные#гироскутеры для девочек',
"Активный отдых/Гироскутеры/8 дюймов"=>'#гироскутеры с приложением#гироскутеры красные#гироскутеры для девочек',
"Активный отдых/Гироскутеры/10 дюймов"=>'#гироскутеры с приложением#гироскутеры красные#гироскутеры для девочек#гироскутеры SUV',
"Активный отдых/Гироскутеры/10.5 дюймов"=>'#гироскутеры с приложением#гироскутеры красные#гироскутеры для девочек#гироскутеры Premium',
"Активный отдых/Ролики"=>'#ролики раздвижные детские#ролики для мальчиков#ролики для девочек#роликовые коньки с защитой#роликовые коньки для фитнеса',
"Активный отдых/Скейты"=>'#круизеры#пенни борды#лонгборды#вейвборды#скейтборды для детей#скейтборды для начинающих#скейтборды для девочек#скейтборды для мальчиков',
"Спортивные игры/Футбол/Бутсы"=>'#бутсы детские#бутсы недорогие#футзалки#бутсы Месси#бутсы сороконожки#бутсы мужские#бутсы черные#бутсы с шипами#бутсы профессиональные#бутсы белые#бутсы для газона#кеды',
"Спортивные игры/Футбол/Мячи футбольные"=>'#футбольные мячи с рисунком#футбольные мячи чемпионата#футбольные мячи белые#футбольные мячи для детей#футбольные мячи черные#футбольные мячи недорогие#футбольные мячи Select#футбольные мячи 5 размера#футбольные мячи 4 размера#футбольные мячи Adidas#футбольные мячи Nike',
"Велосипеды"=>'#велосипеды Forward#велосипеды Krostek#велосипеды Merida#велосипеды Roliz#велосипеды Totem#велосипеды BA#велосипеды Мультяшка#велосипеды Lamborghini#велосипеды Lexus#велосипеды Altair#велосипеды четырехколесные#велосипеды от года#велосипеды складные#велосипеды взрослые#велосипеды с ручкой#велосипеды для девочек#велосипеды недорогие#велосипеды спортивные#велосипеды дисковые#велосипеды трайки#велосипеды легкие#велосипеды черные#велосипеды алюминиевые#велосипеды от 2 лет#велосипеды от 9 лет#велосипеды синие#велосипеды гидравлические#велосипеды розовые#велосипеды двухподвесы#велосипеды трюковые#детские велосипеды-коляски#велосипеды желтые#велосипеды в рассрочку',
"Велосипеды/Городские и дорожные"=>'#велосипеды складные#велосипеды взрослые#велосипеды недорогие#велосипеды спортивные#велосипеды дисковые#велосипеды легкие#велосипеды алюминиевые',
"Велосипеды/Горные"=>'#велосипеды недорогие#велосипеды спортивные#велосипеды дисковые#велосипеды легкие#велосипеды алюминиевые#велосипеды гидравлические#велосипеды двухподвесы',
"Велосипеды/Женские"=>'#велосипеды недорогие#велосипеды спортивные#велосипеды дисковые#велосипеды легкие#велосипеды алюминиевые',
"Велосипеды/Детские и подростковые/Подростковые"=>'#велосипеды для девочек#велосипеды недорогие#велосипеды спортивные#велосипеды дисковые#велосипеды легкие#велосипеды алюминиевые#велосипеды двухподвесы',
"Велосипеды/Детские и подростковые/3-х колесные"=>'#велосипеды от года#велосипеды с ручкой#велосипеды для девочек#велосипеды недорогие#велосипеды трайки#велосипеды легкие#велосипеды алюминиевые#велосипеды от 2 лет#детские велосипеды-коляски',
"Велосипеды/Детские и подростковые/2-х и 4-х колесные"=>'#велосипеды от года#велосипеды с ручкой#велосипеды для девочек#велосипеды недорогие#велосипеды легкие#велосипеды алюминиевые#велосипеды от 2 лет#велосипеды от 9 лет',
"Велосипеды/Детские и подростковые"=>'#велосипеды четырехколесные',
"Спортивные игры/Футбол/Форма футбольная"=>'#манишки#форма Барселоны#форма Месси#форма Милана#форма ПСЖ#форма Реал Мадрид#форма Роналдо#форма сборной Германии#форма Челси#футбольная форма для детей',
"Бассейны, пляж, аксессуары/Бассейны"=>'#бассейны каркасные#бассейны интекс#бассейны надувные#бассейны детские#бассейны недорогие#бассейны для дачи#бассейны bestway',

);

foreach($tags as $key => $tag) {
    
    if($tag !== '')
    {
        process_tag($tag,$key);
    }
    
    
    echo "<hr />";
}