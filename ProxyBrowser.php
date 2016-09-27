<?php

namespace nofikoff\proxybrowser;

//use Yii;


// работа с прокси
// работа с каптчой
//
class ProxyBrowser
{


    // делаем задержку во избежании бана источником
    public $timeout = 0;
    // использовать куки для соединения
    public $file_cookies = '';
    // выводить в выдаче служебные заголовки ответа источника
    public $flag_header = 0;
    // логировать действия
    public $debug = 0;
    // удалить весь JS чтобы неб ыло релироект и пр
    public $erase_js = 0;

    // язык браузера по умолчанию
    public $interface_lang = 'en';

    // если попадается бинери контент, возвращаем пустоту
    public $output_mustbe_nobinary = '1';

    // использовать встроенный на сервере ТОР прокси
    public $use_local_tor_proxy = false;

    // редирект ингда вреден, особенно когда архив интернета парсим // можно редирект только внутрни домена по отноистельным ссылкам
    public $disabling_external_refferer = false;


    // использовать встроенный на сервере ТОР прокси
    public $output_in_file = ''; // здесь путь к фалу куда сливать огромный массивы из курла/ опериавка не выдержит


    public function get_http($url)
    {

        $url = $this->url_http_adding($url);
        $domain = $this->domain_from_url($url);


        $result['url'] = $url;
        $result['error'] = false;
        $result['description'] = '';
        $result['result'] = '';
        $result['content-type'] = '';

        if ($this->timeout) sleep($this->timeout);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);


//        if ($this->flag_header) {
        curl_setopt($ch, CURLOPT_HEADER, true);
//        }

        if ($this->file_cookies) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file_cookies);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file_cookies);
        }


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); //300 секнд = 5 минут
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // редирект разрешен, но далее будем анализировать хилре и бокироват результат редиректа на внешний адрес
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


        if ($this->use_local_tor_proxy) {
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
            $fp = fopen($this->output_in_file, "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");

            echo " / get_http Режим вывода в файл / ";

            curl_exec($ch);
            return true;
            return true;
            return true;
            return true;
            return true; // TODO:тут бы обработку ошибок
        }


        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result['header'] = substr($response, 0, $header_size);
        $retval = substr($response, $header_size);

        // если несколько хидеров из за редиректов - опредеим поседний контент тип
        if (preg_match_all('~Content-Type:\s+(.*)~', $result['header'], $d))
            $result['content-type'] = trim($d[1][(sizeof($d[1]) - 1)]);

        //запрещаем редиректы внешние тольео внутрнииие
        if ($this->disabling_external_refferer AND preg_match('~Location: http://([^/]+)/~ui', $result['header'], $d)
        ) {
            //редирет точно не на наш адрес??
            if ($d[1] != $domain) {
                $result['error'] = true;
                $result['description'] = 'redirect detected';
                // херим результат
                $retval = '';
            }

        }

        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        //ОШИБКИ ВЗАИМОИСКЛЮЧАЮЩИЕ
        if ($httpcode <> '200') {
            $result['error'] = true;
            $result['description'] = $httpcode;
            //
        } else if (FALSE === $retval) {
            $result['error'] = true;
            $result['description'] = curl_error($ch);
        }


        //бинерники НЕ пропускать
        if ($this->output_mustbe_nobinary) {
            if (!preg_match('~text/html~i', $result['content-type'])) {
                $result['error'] = true;
                $result['description'] = 'Content-type НЕ text/html для ' . $url;
                $retval = 'BINARY';
            }
//            else if ($this->isBinary($retval)) {
//                // эта функция запасная и работает херово пропускает кучу бинерников и ложно рабатывае на текстах
//                $result['error'] = true;
//                $result['description'] = 'Контет вроде text/html но наша функция определила что это бинарник или Франц или Имспанский текст' . $url;
//                $retval = 'BINARY';
//            }
        }

        //
        if ($this->erase_js) {
            $retval = preg_replace('/(<script[^>]*>.*?<\/script>)/siu', '', $retval);
        }


        $result['result'] = trim($retval);
        $result['http_code'] = $httpcode;
        curl_close($ch);


        return $result;

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
        $url = trim($url, '/ ');
        return preg_replace('/http\:\/\/|https\:\/\//', '', $url);
    }

    static function url_www_cutting($url)
    {
        $url = trim($url, '/ ');
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


}
