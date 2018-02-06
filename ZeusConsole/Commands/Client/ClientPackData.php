<?php
/**
 * Created by PhpStorm.
 * User: zhipeng
 * Date: 16/4/20
 * Time: 下午2:55
 */

namespace ZeusConsole\Commands\Client;

/**
 * 客户端打包信息
 * Class ClientPackData
 * @package ZeusConsole\Commands\Client
 */
class ClientPackData
{
    /**
     * 打包的ID
     * @var string
     */
    private $id;
    /**
     * 开始SVN版本号
     * @var string
     */
    private $from;
    /**
     * 结束SVN版本号
     * @var string
     */
    private $to;
    /**
     * 客户端Lua版本号
     * @var string
     */
    private $clientversion;
    /**
     * 客户端c++版本号
     * @var string
     */
    private $cppversion;
    /**
     * 打包时间
     * @var string
     */
    private $packtime;

    /**
     * csv的资源版本号
     * @var string
     */
    private $csvSvnRevision;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ClientPackData
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return ClientPackData
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return ClientPackData
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientversion()
    {
        return $this->clientversion;
    }

    /**
     * @param string $clientversion
     * @return ClientPackData
     */
    public function setClientversion($clientversion)
    {
        $this->clientversion = $clientversion;
        return $this;
    }

    /**
     * @return string
     */
    public function getCppversion()
    {
        return $this->cppversion;
    }

    /**
     * @param string $cppversion
     * @return ClientPackData
     */
    public function setCppversion($cppversion)
    {
        $this->cppversion = $cppversion;
        return $this;
    }

    /**
     * @return string
     */
    public function getPacktime()
    {
        return $this->packtime;
    }

    /**
     * @param string $packtime
     * @return ClientPackData
     */
    public function setPacktime($packtime)
    {
        $this->packtime = $packtime;
        return $this;
    }

    /**
     * @return string
     */
    public function getCsvSvnRevision()
    {
        return $this->csvSvnRevision;
    }

    /**
     * @param string $csvSvnRevision
     * @return ClientPackData
     */
    public function setCsvSvnRevision($csvSvnRevision)
    {
        $this->csvSvnRevision = $csvSvnRevision;
        return $this;
    }
    

}