<?php

class Curl
{
    /** @var \resource */
    private $handle;



    /**
     * @param string $url
     * @return resource
     */
    public function init($url)
    {
        $this->handle = \curl_init($url);

        return $this->handle;
    }



    public function close()
    {
        \curl_close($this->handle);
    }



    /**
     * @param string $option
     * @param string $value
     */
    public function setOpt($option, $value)
    {
        \curl_setopt($this->handle, $option, $value);
    }



    public function exec()
    {
        return \curl_exec($this->handle);
    }



    public function error()
    {
        return \curl_error($this->handle);
    }
}
