<?php

class CurlHttpClient
{
    /** @var int */
    private $port;

    /** @var string|null */
    private $user;

    /** @var string|null */
    private $password;

    /** @var Curl */
    private $curl;



    /**
     * @param Curl   $curl
     * @param string $url
     * @param int    $port
     * @param string $user
     * @param string $password
     * @internal param int $port
     */
    public function __construct(Curl $curl, $url, $port, $user = null, $password = null)
    {
        $this->curl     = $curl;
        $this->port     = $port;
        $this->user     = $user;
        $this->password = $password;
        $this->curl->init($url);
    }



    public function __destruct()
    {
        $this->curl->close();
    }



    /**
     */
    public function get($request)
    {
        $this->curl->setOpt(CURLOPT_USERPWD, $this->user . ':' . $this->password);
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOpt(CURLOPT_PORT, $this->port);

        return $this->curl->exec();
    }
}
