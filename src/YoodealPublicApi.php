<?php

class Yoodeal
{
    private $_parameters;

    public function __construct($parameters = array())
    {
        $this->_parameters = $parameters;
    }
    
    public function searchDeals($params)
    {
        return $this->doGet('deals', $params);
    }
    
    public function getCategories()
    {
        return $this->doGet('categories', array('lang' => 'it'));
    }
    
    public function getDeal($id)
    {
        return $this->doGet('details', array('id' => $id));
    }
    
    public function getCities($country = 'it')
    {
        return $this->doGet('cities', array('country' => $country));
    }
    
    protected function getUrl($call, $params = array())
    {
        $params = array_merge($params, array(
            'yd.key' => $this->_parameters['api_key'],
        ));
        $collapsedParams = array();
        foreach ($params as $key => $value)
            $collapsedParams[] = urlencode($key) . '=' . urlencode($value);
        return $this->_parameters['public_api_prefix_url'] . $call . '?' . implode('&', $collapsedParams) . '&api.internal';
    }
    
    protected function doGet($call, $params)
    {
        $url = $this->getUrl($call, $params);
        $ch = curl_init($url);
        $this->logGet('CALLING', array($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno)
        {
            $err = curl_error($ch);
            $this->logGet('ERROR', array($url, $errno, $err));
            curl_close($ch);
            throw new \Exception(sprintf("CURL error #%d:\n%s", $errno, $err));
        }
        curl_close($ch);
        $this->logGet('SUCCESS', array($url));
        
        return json_decode($content);
    }

    protected function log($message, $params = array())
    {
        $f = fopen($this->_parameters['log_path'], 'a');
        fputcsv($f,
                array_merge(array(
                    date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'], $message,
                ), $params),
            "\t");
        fclose($f);
    }


    protected function logGet($message, $params = array())
    {
        if ($this->_parameters['log_get'])
            $this->log($message, $params);
    }


}
