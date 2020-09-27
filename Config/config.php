<?php

return [
"DB" => [
    "Host" => "localhost",
    "User" => "root",
    "Password" => "",
    "DBName" => "cinema_rating",
    "Port" => 3306,
    "CharSet" => "utf8"
],

"ParserOptions" => [
    // общий шаблон парсинга, если не задан для конкретного URL
    "template" => "<tr height=20><td bgcolor=#D7D7D7 align=center class='review'><b>(\d{2})<\/b><\/td><td bgcolor=#D7D7D7><a href = \"cinema\.php\?id=(\d+)\" class='review'>([^<]*)<\/a> \[(\d{4})\]&nbsp;&nbsp;<\/td><td bgcolor=#D7D7D7 align=center class='review'>(\d\.\d{4})<\/td><td bgcolor=#D7D7D7 align=center class='review'><a href = \"votes_history\.php\?id=\d+\" target='new' class='review'>(\d+)<\/a><\/td><td bgcolor=#D7D7D7 align=center class='review'>(\d\.\d)<\/td><\/tr>",
    // общая кодировка получаемых URL, если не задан для конкретного URL
    "code_page" => "windows-1251",
    // поля из БД в том порядке, 
    // в каком они вернуися парсером после обработки в соответствии с шаблоном (template)
    "groups" => [
//        "full" => "вся информация",
        "place" => "место",
        "id" => "ID world-art",
        "title" => "название фильма",
        "year" => "год",
        "grade" => "расчетный балл",
        "voites" => "всего лосов",
        "average_grade" => "Средний балл"
    ],
    // дополнительные поля для получения данных (картинка, описание) по ID ресурса
    "data" => [
        "url" => "http://www.world-art.ru/cinema/cinema.php?id={id}",
        "fields" => [
            "picture" => "<div class='comment_block'><table width=300 height=400><tr><td><a href='http:\/\/www\.world-art\.ru\/cinema\/cinema_poster\.php\?id=\d+&poster_number=\d+' title='[^']+'><img src='([^']+)' width=300 border=1 alt='[^']+'><\/a>",
            "description" => "<tr><td height=1 width=100% bgcolor=#eaeaea><\/td><\/tr><\/table><table width=100%><tr><td><p align=justify class='review'>(.+)<\/p><\/td><\/tr>"
        ],
    ],
    // список URL, а так же индивидуальные настройки
    "urls" =>[
        [
            "url" => "http://www.world-art.ru/cinema/rating_top.php",
            "category_id" => 5,
            "title" => "Рейтинг полнометражных фильмов",
            "template" => "<tr height=20><td bgcolor=#D7D7D7 align=center class='review'><b>(\d{2})<\/b><\/td><td bgcolor=#D7D7D7><a href = \"cinema\.php\?id=(\d+)\"  class='review'>([^<]+)<\/a> \[(\d{4})\]&nbsp;&nbsp;<\/td><td bgcolor=#D7D7D7 align=center  class='review'>(\d\.\d{4})<\/td><td bgcolor=#D7D7D7 align=center><a href = \"votes_history\.php\?id=\d+\" target='new'  class='review'>(\d+)<\/a><\/td><td bgcolor=#D7D7D7 align=center  class='review'>(\d.\d)<\/td><\/tr>"
        ],
        [
            "url" => "http://www.world-art.ru/cinema/rating_tv_top.php?public_list_anchor=1",
            "category_id" => 1,
            "title" => "Рейтинг западных сериалов"
        ],
        [
            "url" => "http://www.world-art.ru/cinema/rating_tv_top.php?public_list_anchor=2",
            "category_id" => 2,
            "title" => "Рейтинг японских дорам"
        ],
        [
            "url" => "http://www.world-art.ru/cinema/rating_tv_top.php?public_list_anchor=4",
            "category_id" => 4,
            "title" => "Рейтинг корейских дорам"
        ],
        [
            "url" => "http://www.world-art.ru/cinema/rating_tv_top.php?public_list_anchor=3",
            "category_id" => 3,
            "title" => "Рейтинг российских сериалов"
        ]
    ]
],
    
"CURLHeaders" => [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:81.0) Gecko/20100101 Firefox/81.0",
    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
    "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3",
    "Accept-Encoding: gzip, deflate",
    "DNT: 1",
    "Connection: keep-alive",
    "Upgrade-Insecure-Requests: 1",
    "Cache-Control: max-age=0"
],
    
"TmpFileName" => "http_content.txt",
"ImagesFolder" => "/Images",
"ImagesURLPrefix" => "http://www.world-art.ru/cinema/",
"StartCatalog" => ""

];
