<?php

namespace nofikoff\proxybrowser;

//use Yii;


// работа с прокси
// работа с каптчой
//
class Yii2ProxyBrowser
{


    // делаем задержку во избежании бана источником
    public $timeout = 0;
    // использовать куки для соединения
    public $file_cookies = '';
    // выводить в выдаче служебные заголовки ответа источника
    public $flag_header = 0;
    // логировать действия
    public $debug = 0;


    public function get_http ($url)
    {
        if ($this->timeout) sleep($this->timeout);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($this->flag_header) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if ($this->file_cookies) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file_cookies);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file_cookies);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); //300 секнд = 5 минут


        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'If-None-Match: "b794ca67caad8184743a04f87a81a4dd"',
                'DNT: 1',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Cache-Control: max-age=0',
            ]
        );


        $retval = curl_exec($ch);
        /*
        if (FALSE === $retval AND $this->debug) {
            SystemMessagesLogController::Save(
            //0 просто мессадж серым
            //1 ключевое сообщение зеленым
            //2 красный варанинг
            //3 красный ЖИРНЫМ фатал
                2,
                "http_get_contents",
                "file_get_content by curl $url : " . curl_error($ch)
            );

        } else {
            return $retval;
        }*/
        return $retval;

    }



}
