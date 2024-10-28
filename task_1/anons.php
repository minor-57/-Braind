<?php

    function getPageText($html){
        $result = '';
        $dom = new DomDocument();
        $dom->loadHTML($html);
        $dom->preserveWhiteSpace = false;
        $content = $dom->getElementsByTagName('p');
        foreach($content as $each){
            $result = $result . $each->nodeValue . ' ';
        }
        return $result;
    }
    function preview($text, $limit = 250){
        $text = stripcslashes($text);                                                  // удаляем экранирующие обратные слеши
        $text = htmlspecialchars_decode($text, ENT_QUOTES);                     // преобразуем спецсимволы html в обычные
        $text = str_ireplace(array('<br/>', '<br />'), ' ', $text);  // удаляем самозакрывающиеся теги, тк strip_tags их игнорирует
        $text = strip_tags($text);                                                     // удаляем html теги
        $text = trim($text);

        if (mb_strlen($text) < $limit){                                                 // если текст статьи короче чем макс. длина анонса, возвращаем текст без обрезки
            return $text;
        } else {
            $text = mb_substr($text, 0, $limit);                           // обрезаем текст до макс. длины анонса
            $length = mb_strripos($text, ' ');                                   // находим последнее вхождение пробела
            $end = mb_substr($text, $length - 1, 1);                        // находим символ на который заканчивается последнее необрезанное слово

            if (empty($length)){
                return $text;
            } elseif (in_array($end, array('.', '!', '?'))){                    // если последний символ '.', '!' или '?', троеточие не добавляем
                return mb_substr($text, 0, $length);
            } elseif (in_array($end, array(',', ':', ';', '«', '»', '…', '(', ')', '—', '–', '-'))){
                return mb_substr($text, 0, $length - 1) . '...';            // если последний символ ,', ':', ';', '«', '»', '…', '(', ')', '—', '–' или '-', удаляем его и добаваляем троеточие
            } else {
                return mb_substr($text, 0, $length) . '...';                // если последний символ не знак препинания, возвращаем обрезанный текст и добавляем троеточие
            }
        }
    }

    function insertLink($text, $link = '', $length = 3){                            //вставка ссылки
        $words = explode(' ', $text);                                    //делим текст на массив слов
        $size = sizeof($words);
        $result = '';
        if ($size < $length){
            return "<a href=\"$link\">" . $text . '</a>';
        } else {
            for($i = 0; $i < $size; $i++){                                          //объединяем слова в строку пока не дойдем до 3 (или заданного) с конца
                if($i == ($size - $length - 1)){
                    $result = $result . "<a href=\"$link\">" . $words[$i] . ' ';
                } elseif ($i == $size - 1){
                    $result = $result . $words[$i] . '</a>';
                } else {
                    $result = $result . $words[$i] . ' ';
                }
            }
            return $result;
        }
    }
    $content = getPageText(file_get_contents('articles/test.html'));
    echo insertLink(preview($content), 'articles/test.html');
