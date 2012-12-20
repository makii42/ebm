<?php
/**
 * As usually when prototyping, needs proper implementation
 */

class Config
{
    /** @var stdClass */
    private $data;


    /**
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        $this->data = json_decode(file_get_contents($configFile));
    }


    /**
     * @param string $key
     * @param null $defaultValue
     * @return null|mixed
     */
    public function get($key, $defaultValue = null)
    {
        if (preg_match('/\./', $key)) {
            return $this->getNestedValue($key, $this->data, $defaultValue);
        }

        return $this->getSimpleValue($key, $this->data, $defaultValue);
    }


    /**
     * @param string $key
     * @param stdClass $data
     * @param mixed $defaultValue
     * @return mixed
     */
    private function getSimpleValue($key, $data, $defaultValue)
    {
        if ((is_numeric($key) && !array_key_exists($key, $data)) || (!is_numeric($key) && !isset($data->$key))) {
            return $defaultValue;
        }

        return is_numeric($key) && array_key_exists($key, $data) ? $data[$key] : $data->$key;
    }


    private function getNestedValue($key, $data, $defaultValue)
    {
        $parts     = explode('.', $key);
        $firstPart = $parts[0];

        if (((is_numeric($firstPart) && array_key_exists($firstPart, $data)) || isset($data->$firstPart)) && count($parts) > 1) {
            return $this->getNestedValue(substr($key, strpos($key, '.') + 1), is_numeric($firstPart) ? $data[$firstPart] : $data->$firstPart, $defaultValue);
        }

        return $this->getSimpleValue($firstPart, $data, $defaultValue);
    }


    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->data);
    }
}
