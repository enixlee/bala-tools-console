<?php
/**
 * Created by PhpStorm.
 * User: enixlee
 * Date: 2016/12/15
 * Time: 下午9:25
 */

namespace ZeusConsole\Commands\miniPay\CodeGenerate\JSCodeGenerate;


class RpcGenerateJSClass
{
    private $urls = [];

    public function getUrlsDescription()
    {
        $description = '';
        foreach ($this->urls as $key => $url) {
            $cmd = "    " . $key . ':';
            $urlStr = '"' . $url['url'] . '"';
            $des = '   {  url: ' . $urlStr . ", method:   \"" . $url['method'] . "\"  }, " . '    //' . $url['comment'] . "\n";
            $description .= ($cmd . $des);
        }

        return $description;
    }

    /**
     *
     * @param $urlNameKey
     * @param $url
     * @param $method
     * @param $comment
     */
    public function addUrl($urlNameKey, $url, $method, $comment)
    {
        $this->urls[$urlNameKey] = ['url' => $url, 'comment' => $comment, 'method' => $method];
    }
}