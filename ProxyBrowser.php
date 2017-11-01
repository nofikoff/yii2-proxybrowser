<?php
/*
Мой браузер 
прокси
антигейт
работа с URL

парсер хтмл смотри в другом классе
*/


namespace nofikoff\proxybrowser;

use app\controllers\SystemMessagesLogController;
use app\controllers\TestController;
use Yii;


// работа с прокси
// работа с каптчой
//
class ProxyBrowser
{

    public $result =
        [
            'url' => '',
            'error' => false,
            'description' => '',
            'code' => 0,
            'http_code' => 0,
            'raw' => '',
            'header' => '',
            'content-type' => '',
        ];

    // делаем задержку во избежании бана источником
    public $timeout = 0;

    public $timewaitconnect = 60;

    // использовать куки для соединения
    public $file_cookies = '';
    // выводить в выдаче служебные заголовки ответа источника
    // НЕ ВЫКЛЮЧАТЬ кроме момента качания файла зашлово далее убирается регулярным выржением!!!!
    public $flag_header = 1;
    // логировать действия
    public $debug = 0;
    // удалить весь JS чтобы неб ыло релироект и пр
    public $erase_js = 1;

    // язык браузера по умолчанию
    public $interface_lang = 'en';

    // если попадается бинери контент, возвращаем пустоту
    public $output_mustbe_html = '0';

    // использовать встроенный на сервере ТОР прокси
    //http://localhost:9050
    public $use_local_tor_proxy = false;

    // использовать мои php proxy
    public $use_my_external_php_proxy = false;

    // редирект ингда вреден, особенно когда архив интернета парсим // можно редирект только внутрни домена по отноистельным ссылкам
    public $disabling_external_refferer = false;


    // использовать встроенный на сервере ТОР прокси
    public $output_in_file = ''; // здесь путь к фалу куда сливать огромный массивы из курла/ опериавка не выдержит
    public $output_in_file_need_Ungzip = 1; // файл желаиелтно рапсковать


    public $use_antigate = 0;

    static function checkExternalFile($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $header = curl_exec($ch);

        $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['content_type'] = '';
        $result['header'] = $header;
        curl_close($ch);
        // если несколько хидеров из за редиректов - опредеим поседний контент тип
        if (preg_match('~Content-Type:\s+(.*)~i', $header, $d))
            $result['content_type'] = $d[1];
        else print_r($result);
        return $result;
    }
    //200
    //400
    //image - pregmatch в результате потом искать
    //text


    public function get_http($url)
    {


        // Курл не любит нульбайтовые знаки в урл - ингда изАрхива интернет прорываются
        $url = str_replace("\0", "", $url);


        $this->result['url'] = $url;
        $url = $this->url_http_adding($url);
        //
        $url_params = '';
        $a = explode("?", $url, 2);
        $url_page = $a[0];
        if (isset($a[1])) $url_params = $a[1];

        $domain = $this->domain_from_url($url);


        //прокси на пхп
        //прокси на пхп
        //прокси на пхп
        //прокси на пхп
        //прокси на пхп
        if ($this->use_my_external_php_proxy) {
            // получаем из спика проксей одного случаным образом
            preg_match_all('~([^\s]+)~', Yii::$app->get('settings')->get('system.ListPhpProxy'), $d);
            shuffle($d[1]);

            $proxy_url = trim(array_shift($d[1]), " ',");
            $data = $this->php_proxy_client($proxy_url, $url_page, $url_params, 'get');
            echo $data['description'] .= 'PHP Proxy: ' . $proxy_url;
            //
            if ($this->erase_js) {
                $data['raw'] = preg_replace('/(<script[^>]*>.*?<\/script>)/siu', '', $data['raw']);
            }


            // двйное резервирование - если ошбка - еще одна попытка
            // двйное резервирование - если ошбка - еще одна попытка
            // двйное резервирование - если ошбка - еще одна попытка
            // двйное резервирование - если ошбка - еще одна попытка
            if ($data['error']) {
                SystemMessagesLogController::Save(
                //0 просто мессадж серым
                //1 ключевое сообщение зеленым
                //2 красный варанинг
                //3 красный ЖИРНЫМ фатал
                    3,
                    "ProxyBrowser get_http External php proxy",
                    "\nДВОЙНОЕ РЕЗЕРВИРОВАНИЕ **** Прокси сдох, выдал ошибку {$data['description']}",
                    $data['raw']
                );
                $proxy_url = trim(array_shift($d[1]), " ',");
                $data = $this->php_proxy_client($proxy_url, $url_page, $url_params, 'get');
                $data['description'] .= ' Proxy: ' . $proxy_url;
                //
                if ($this->erase_js) {
                    $data['raw'] = preg_replace('/(<script[^>]*>.*?<\/script>)/siu', '', $data['raw']);
                }
            }

            // тройное резервирование - если ошбка - еще одна попытка
            // тройное резервирование - если ошбка - еще одна попытка
            // тройное резервирование - если ошбка - еще одна попытка
            // тройное резервирование - если ошбка - еще одна попытка
            if ($data['error']) {
                SystemMessagesLogController::Save(
                //0 просто мессадж серым
                //1 ключевое сообщение зеленым
                //2 красный варанинг
                //3 красный ЖИРНЫМ фатал
                    3,
                    "ProxyBrowser get_http External php proxy",
                    "\nТРОЙНОЕ РЕЗЕРВИРОВАНИЕ **** Прокси сдох, выдал ошибку {$data['description']}",
                    $data['raw']
                );
                $proxy_url = trim(array_shift($d[1]), " ',");
                $data = $this->php_proxy_client($proxy_url, $url_page, $url_params, 'get');
                $data['description'] .= ' Proxy: ' . $proxy_url;
                //
                if ($this->erase_js) {
                    $data['raw'] = preg_replace('/(<script[^>]*>.*?<\/script>)/siu', '', $data['raw']);
                }
            }

            //жалуемся Оператору
            if ($data['error']) {
                SystemMessagesLogController::Save(
                //0 просто мессадж серым
                //1 ключевое сообщение зеленым
                //2 красный варанинг
                //3 красный ЖИРНЫМ фатал
                    3,
                    "ProxyBrowser get_http External php proxy",
                    "\n**** Прокси сдох, выдал ошибку {$data['description']}",
                    $data['raw']
                );
            }


            return $data;
            return $data;
            return $data;
            return $data;
            return $data; //TODO: тут гдето обработку ошибок
        }

        // конец прокси на пхп


        if ($this->timeout) sleep($this->timeout);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);


        if ($this->flag_header AND !$this->output_in_file) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if ($this->file_cookies) {
            echo "<h1>USING COOKIES {$this->file_cookies}</h1>";
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file_cookies);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file_cookies);
        }


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timewaitconnect); //300 секнд = 5 минут
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // редирект разрешен, но далее будем анализировать хилре и бокироват результат редиректа на внешний адрес
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


        if ($this->use_local_tor_proxy) {

            //service tor restart
            $a = ProxyBrowser::checkExternalFile('http://localhost:9050');
            //здравствуй жопа новый год
            //здравствуй жопа новый год
            if ($a['http_code'] == 0) {
                SystemMessagesLogController::Save(
                //0 просто мессадж серым
                //1 ключевое сообщение зеленым
                //2 красный варанинг
                //3 красный ЖИРНЫМ фатал
                    3,
                    "ProxyBrowser get_http External php proxy",
                    "'Need service tor restart Die http://localhost:9050",
                    $a['header']
                );
                return [
                    'error' => true,
                    'description' => 'Need service tor restart Die http://localhost:9050',
                    'code' => 0,
                    'http_code' => 0,
                ];
            }
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXY, 'http://localhost:9050');
        }


        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'If-None-Match: "b794ca67caad8184743a04f87a81a4dd"',
                'DNT: 1',
                'Accept-Language: ' . $this->interface_lang . ', en-gb;q=0.8, en;q=0.7',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Cache-Control: max-age=0',
            ]
        );


        if ($this->output_in_file) {
            if (!$fp = fopen($this->output_in_file, "w")) {
                unlink($this->output_in_file);
                $fp = fopen($this->output_in_file, "w");
            }
            curl_setopt($ch, CURLOPT_FILE, $fp);
            if ($this->output_in_file_need_Ungzip) // разаврхивируем
                curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            echo " / get_http Режим вывода в файл / ";
            curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contlen = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            $contlen2 = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
            curl_close($ch);
            fclose($fp);
//            chmod($this->output_in_file, 0777); тут сбой типа прав нету нахер надо менять?


            //опытным путем уставноил что минимальны размер пустого файла после скачивания 20
            if ($contlen2 < 25) {
                echo "<h4>Proxy Browser - При попытке скачать файл - получили пустое содержимое";
                return [
                    'error' => 1,
                    'file_name' => $this->output_in_file,
                    'httpcode' => $httpcode,
                    'contlen' => $contlen,
                    'contlen2' => $contlen2,
                ];
            }


            return [
                'error' => 0,
                'file_name' => $this->output_in_file,
                'httpcode' => $httpcode,
                'contlen' => $contlen,
                'contlen2' => $contlen2,
            ];

            return true;
            return true;
            return true;
            return true;
            return true; // TODO:тут бы обработку ошибок
        }


        // МЯСО!!!
        $response = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($this->flag_header) {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $this->result['header'] = substr($response, 0, $header_size);
            $retval = substr($response, $header_size);
        } else {
            $this->result['header'] = 'NA';
            $retval = $response;
        }


        // если несколько хидеров из за редиректов - опредеим поседний контент тип
        if (preg_match_all('~Content-Type:\s+(.*)~', $this->result['header'], $d))
            $this->result['content-type'] = trim($d[1][(sizeof($d[1]) - 1)]);

        //запрещаем редиректы внешние тольео внутрнииие
        if ($this->disabling_external_refferer
            AND preg_match_all('/Location: ([^\/].*)/ui', $this->result['header'], $d)
        ) {
            foreach ($d[1] as $to_address) {
                $to_address = $this->url_http_cutting(trim($to_address));
                //редирет точно не на наш адрес??
                if ($to_address != $domain) {
                    $this->result['error'] = true;
                    $this->result['description'] = 'redirect detected';
                    $this->result['code'] = 302;
                    $this->result['http_code'] = 302;
                    $this->result['raw'] = $retval;
                    // херим результат
                    return $this->result;
                }
            }
        }


        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        if ($httpcode <> '200') {
            $this->result['error'] = true;
            $this->result['description'] = $httpcode;
            $this->result['code'] = $httpcode;
            $this->result['http_code'] = $httpcode;
            //
        } else if (FALSE === $retval) {
            // тут в том числе пустой контент и 200 код
            $this->result['error'] = true;
            $this->result['description'] = curl_error($ch);
            $this->result['code'] = 0;
            $this->result['http_code'] = 0;
        }


        //бинерники НЕ пропускать
        if ($this->output_mustbe_html) {
            if (!preg_match('~text/html~i', $this->result['content-type'])) {
                $this->result['error'] = true;
                $this->result['description'] = 'Content-type НЕ text/html для ' . $url;
                $this->result['code'] = 200;
                $this->result['http_code'] = 200;
                $retval = 'NOHTML';
            }
//            else if ($this->isBinary($retval)) {
//                // эта функция запасная и работает херово пропускает кучу бинерников и ложно рабатывае на текстах
//                $this->result['error'] = true;
//                $this->result['description'] = 'Контет вроде text/html но наша функция определила что это бинарник или Франц или Имспанский текст' . $url;
//                $retval = 'BINARY';
//            }
        }

        //
        if ($this->erase_js) {
            $retval = preg_replace('/(<script[^>]*>.*?<\/script>)/siu', '', $retval);
//            $retval = str_replace('<script', '<', $retval);
//            $retval = str_replace('<\script', '<\\', $retval);
        }


        $this->result['raw'] = trim($retval);
        $this->result['code'] = $httpcode;
        $this->result['http_code'] = $httpcode;
        curl_close($ch);


        if ($this->use_antigate) {
            //проверяем не получили ли мы каптчу
            if ($this->result['http_code'] == '503' AND preg_match('~To continue, please type the characters below~u', $this->result['raw'])) {
                // меняет $this->result
                echo "ProxyBrowser каптчу ВИЖУ";
                $this->get_http_google_antigate();

            } // Google
            else {
                echo "ProxyBrowser каптчу не вижу, все ОК";
                //echo $this->result['raw'];
                echo "<hr>\n";
            }
        }


        return $this->result;

    }

    private function get_http_google_antigate()
    {


        // в зависимоти от IP6 IP4 адрес гугла домена меняется
        if (!preg_match('~Location:\shttps{0,1}://([^/]+)/~i', $this->result['header'], $d)) {
            echo '<h1>Не могу определить на каокм адресе каптча</h1>';
            return false;
        }

        $google_sorry_domain = $d[1];

        echo "\n\n\n\n\n\n\n\n\n\n\n Вызываем Антигейт - СОВЕТ, удали куки, если тут что то начинает глючить ;) <br><br><br><br><br>";

        echo 'ХИДЕР ' . $this->result['header'];
        echo "****************************************\n\n\n\n\n\n\n\n\n\n\n<br><br><br><br><br>";

        echo 'URL ' . $this->result['url'];
        echo "****************************************\n\n\n\n\n\n\n\n\n\n\n<br><br><br><br><br>";

        echo 'RAW ' . $this->result['raw'];

        echo "****************************************\n\n\n\n\n\n\n\n\n\n\n<br><br><br><br><br>";

        // Это 100% гугл каптча
        if (!preg_match('~img src="([^"]+?)"~i', $this->result['raw'], $d)) {
            echo "11111111111111";
            echo $this->result['raw'];
            exit;
        }
        $img_url = 'http://www.google.com' . str_replace('&amp;', '&', $d[1]);
        //Мы хотим скопировать ее себе на сервер и дать имя “some_image.jpg”
        $local_img_file = "./_logs/captcha.jpg";
        //режим вывдоа в айл
        $this->output_in_file = "./_logs/captcha.jpg";
        //распкаоывать файл не надо
        $this->output_in_file_need_Ungzip = 0;
        //выводи в указанный файл
        $this->get_http($img_url);
        //отключаем режим вывод в файл
        $this->output_in_file = "";


        //для ответа номер каптчи
        // МИГАЮЩИЙ КОМПОНЕНТ, был и у Макса в парсерах и у мои програмстов пару лет назад
        if (!preg_match("~id=(.*?)&~i", $img_url, $d)) {
            echo "ididididididididididididididididididididididididididididididididididididididididididididididid";
            echo $this->result['raw'];
            exit;
        }
        $id_captcha = urlencode($d[1]);


        //для ответа q внимание тут подъебка - одинарные кавчки
        if (!preg_match("~name='q' value='([^']+?)'~", $this->result['raw'], $d)) {
            echo "qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq";
            echo $this->result['raw'];
            exit;
        }
        $secret_q = urlencode($d[1]);

        //для ответа continue
        if (!preg_match('~name="continue" value="([^"]+?)"~', $this->result['raw'], $d)) {
            echo "continuecontinuecontinuecontinuecontinuecontinuecontinuecontinuecontinuecontinuecontinuecontinue";
            echo $this->result['raw'];
            exit;
        }
        $continue = urlencode(str_replace('&amp;', '&', $d[1]));


        //распознаем
        $text = $this->recognize(
            $local_img_file,
            Yii::$app->get('settings')->get('system.AntigateAPIKey'),
            $antg_debug = 1,
            "antigate.com",
            $rtimeout = 5,
            $mtimeout = 120,
            $is_phrase = 0,
            $is_regsense = 1,
            $is_numeric = 0,
            $min_len = 6,
            $max_len = 8,
            $is_russian = 0
        );

        if ($text != '') {
            if ($text == 'error_captcha_unsolvable' OR $text == 'ERROR_NO_SLOT_AVAILABLE') {
                // НЕ ШМОГЛА ;)
                SystemMessagesLogController::Save(
                //0 просто мессадж серым
                //1 ключевое сообщение зеленым
                //2 красный варанинг
                //3 красный ЖИРНЫМ фатал
                    3,
                    "ProxyBrowser google antigate",
                    "Antigate занят, каптчу Гугла не распознали"
                );
                $this->result['error'] = true;
                $this->result['description'] = 'antigate error_captcha_unsolvable';
                $this->result['code'] = 503;
                $this->result['http_code'] = 503;

            } else {

                // есть ответ покаптче
                //http://ipv6.google.com/sorry/index

                if ($google_sorry_domain =='ipv6.google.com'){
                    $get_c = 'http://' . $google_sorry_domain . '/sorry/index?q=' . $secret_q . '&captcha=' . $text . '&continue=' . $continue . "&submit=Submit";
                }
                else {
                    $get_c = 'http://' . $google_sorry_domain . '/sorry/CaptchaRedirect?q=' . $secret_q . '&captcha=' . $text . '&continue=' . $continue . "&submit=Submit";
                }

                $r = $this->get_http($get_c);

                print_r($r);
                echo '<h1>Вроде прошли каптчу - перегрузи старницу!!!!!</h1>';
                //чистим куки, ибо часто и за них потом траблы
                //unlink($this->file_cookies);


            }
        } else {
            // НЕ ШМОГЛА ;)
            SystemMessagesLogController::Save(
            //0 просто мессадж серым
            //1 ключевое сообщение зеленым
            //2 красный варанинг
            //3 красный ЖИРНЫМ фатал
                3,
                "ProxyBrowser google antigate",
                "Нет ответа от антигейта"
            );

            $this->result['error'] = true;
            $this->result['description'] = 'google empty response antigate';
            $this->result['code'] = 503;
            $this->result['http_code'] = 503;
        }
    }


    // Вытащим доменное имя -- газвание проекта без WWW
    static function domain_from_url($url)
    {
        $url = preg_replace('/http\:\/\/|https\:\/\//ui', '', trim($url));
        $url = preg_replace('/www\./', '', $url);
        $url = explode("/", $url);
        return $url[0];
    }


    // добавим http протокол к доменному имени
    static function url_http_adding($url)
    {
        $url_a = parse_url($url);
        if (!isset($url_a['scheme']))
            $url = 'http://' . $url;
        return $url;
    }


    static function url_http_cutting($url)
    {
        //$url = trim($url, '/ ');  !!!! какого хера я это делал - ломал ссылки ?????
        return preg_replace('/http\:\/\/|https\:\/\//', '', $url);
    }

    static function url_www_cutting($url)
    {
        //$url = trim($url, '/ '); !!!! какого хера я это делал - ломал ссылки ?????
        return preg_replace('/www\./', '', $url);
    }


    static function haveSeldomNotEngRusChars($str)
    {
        // Иногда ошибочно срабоатывает на Френч букву и пр Восточные НЕ англ языки
        // тут например http://web.archive.org/web/20060517230819id_/http://www.finance-solidaire.info/article.php3?id_article=13

        preg_match('~[^\x20-\x7E\t\r\n]~', $str, $d);
        //print_r($d);
        if (sizeof($d)) {
            return true;
        }
        return false;
    }

    //ANTIGATE
    /*
    $filename - file path to captcha
    $apikey   - account's API key
    $rtimeout - delay between captcha status checks
    $mtimeout - captcha recognition timeout

    $is_verbose - false(commenting OFF),  true(commenting ON)

    additional custom parameters for each captcha:
    $is_phrase - 0 OR 1 - captcha has 2 or more words
    $is_regsense - 0 OR 1 - captcha is case sensetive
    $is_numeric -  0 OR 1 - captcha has digits only
    $min_len    -  0 is no limit, an integer sets minimum text length
    $max_len    -  0 is no limit, an integer sets maximum text length
    $is_russian -  0 OR 1 - with flag = 1 captcha will be given to a Russian-speaking worker

    usage examples:
    $text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",true, "antigate.com");
    $text=recognize("/path/to/file/captcha.jpg","YOUR_KEY_HERE",false, "antigate.com",1,0,0,5);

    */
    static function recognize(
        $filename,
        $apikey,
        $is_verbose = true,
        $domain = "antigate.com",
        $rtimeout = 5,
        $mtimeout = 120,
        $is_phrase = 0,
        $is_regsense = 0,
        $is_numeric = 0,
        $min_len = 0,
        $max_len = 0,
        $is_russian = 0
    )
    {
        if (!file_exists($filename)) {
            if ($is_verbose) echo "<h4>file $filename not found\n";
            return false;
        }
        $postdata = array(
            'method' => 'post',
            'key' => $apikey,
            'file' => '@' . $filename,
            'phrase' => $is_phrase,
            'regsense' => $is_regsense,
            'numeric' => $is_numeric,
            'min_len' => $min_len,
            'max_len' => $max_len,

        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://$domain/in.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            if ($is_verbose) echo "<h4>CURL returned error: " . curl_error($ch) . "\n";
            return false;
        }
        curl_close($ch);
        if (strpos($result, "ERROR") !== false) {
            if ($is_verbose) echo "<h4>server returned error: $result\n";
            return false;
        } else {
            $ex = explode("|", $result);
            $captcha_id = $ex[1];
            if ($is_verbose) echo "<h4>captcha sent, got captcha ID $captcha_id\n";
            $waittime = 0;
            if ($is_verbose) echo "<h4>waiting for $rtimeout seconds\n";
            sleep($rtimeout);
            while (true) {
                $result = file_get_contents("http://$domain/res.php?key=" . $apikey . '&action=get&id=' . $captcha_id);
                if (strpos($result, 'ERROR') !== false) {
                    if ($is_verbose) echo "<h4>server returned error: $result\n";
                    return false;
                }
                if ($result == "CAPCHA_NOT_READY") {
                    if ($is_verbose) echo "<h4>captcha is not ready yet\n";
                    $waittime += $rtimeout;
                    if ($waittime > $mtimeout) {
                        if ($is_verbose) echo "<h4>timelimit ($mtimeout) hit\n";
                        break;
                    }
                    if ($is_verbose) echo "<h4>waiting for $rtimeout seconds\n";
                    sleep($rtimeout);
                } else {
                    $ex = explode('|', $result);
                    if (trim($ex[0]) == 'OK') return trim($ex[1]);
                }
            }

            return false;
        }
    }



    // общается с пхп скпритом на разных моих хостингах примитивный прокси
    function php_proxy_client($proxy_url, $url_need_service, $param_req_need_service, $method)
    {


        // адрес на удленном сервере !!!!!!!!!!!!!!!!!!!
        $proxy_url .= 'proxy.php';

        // данные если содержат спецзнаки типа & ? обящательтноо уроенкодировать
        // остальное не обзяательно, т.к. ри передаче пост методом на прокис они с той стооны автоматом вылазят декодированными
        $param_req_need_service = $param_req_need_service . '&proxy_method=' . $method . '&proxy_url=' . $url_need_service;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $proxy_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_req_need_service);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $a = trim(curl_exec($ch));
        curl_close($ch);


        $result = json_decode($a, true);

        if (!isset($result['error'])) {
            $result['error'] = 1;
            $result['description'] = 'Proxy problem ' . $proxy_url;
            $result['raw'] = $a;
        }
        return $result;
    }

    static function title_parse_raw_html($html)
    {
        if (preg_match('~<title>([^<]+?)<\/title>~', $html, $d)) {
            $r = [
                'error' => false,
                'description' => '!',
                'result' => $d[1]
            ];
        } else {
            $r = [
                'error' => true,
                'description' => 'Не вижу тайтла',
                'result' => ''
            ];
        }
        //
        return $r;

    }

    static function RemoveEmptySubFolders($path)
    {
        $empty = true;
        foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
            if (is_dir($file)) {
                if (!ProxyBrowser::RemoveEmptySubFolders($file)) $empty = false;
            } else {
                $empty = false;
            }
        }
        if ($empty) rmdir($path);
        return $empty;
    }

}
