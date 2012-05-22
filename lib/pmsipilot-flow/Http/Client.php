<?php

class Http_Client
{
  /**
   * @var null
   */
  private $cookieJar = null;
  
  /**
   * @var array
   */
  private $headers = array();
  
  /**
   * 
   */
  const HTTP_POST = 'POST';
  
  /**
   * 
   */
  const HTTP_GET = 'GET';
  
  /**
   * 
   */
  public function __construct()
  {
  }
  
  /**
   * Make a POST request
   * @param $url string
   * @param array $fields
   * @return Http_Response
   */
  public function post($url, $fields = array())
  {
    return $this->hit($url, $fields, self::HTTP_POST);
  }
  
  /**
   * Make a GET request
   * @param $url
   * @param array $fields
   * @return Http_Response
   */
  public function get($url, $fields = array())
  {
    return $this->hit($url, $fields, self::HTTP_GET);
  }
  
  /**
   * Make an HTTP request
   * @throws RuntimeException
   * @param $url string
   * @param array $fields
   * @param string $method
   * @return Http_Response
   */
  private function hit($url, $fields = array(), $method = self::HTTP_GET)
  {
    $this->initCookieJar();
    
    $ch = curl_init();
    
    // set URL and other appropriate options
    $options = array(
      CURLOPT_URL              => $url,
      CURLOPT_RETURNTRANSFER   => 1,
      CURLOPT_COOKIEJAR        => $this->cookieJar,
      CURLOPT_COOKIEFILE       => $this->cookieJar,
      CURLOPT_VERBOSE          => 0, 
      CURLOPT_HEADERFUNCTION   => array($this, 'readHeader'),
    );
    
    if (self::HTTP_POST === $method)
    { 
      if (count($fields))
      {
        // We look for files to upload (to avoid http_build_query call)
        $withFile = false;
        foreach ($fields as $fieldValue)
        {
          if (is_array($fieldValue))
          {
            // No file uploads on multidimension arrays
            // If you want many dimensions on the key, you have to define $fields this way:
            // $fields = array('my_files[my_file]' => '@path');
            // instead of: 
            // $fields = array('my_files' => array('my_file' => '@path'));
            break;
          }
          elseif (substr($fieldValue, 0, 1)==='@' && is_file(substr($fieldValue, 1)))
          {
            $withFile = true;
          }
        }
        
        if ($withFile)
        {
          $options[CURLOPT_POSTFIELDS] = $fields;
        }
        else
        {
          $options[CURLOPT_POSTFIELDS] = http_build_query($fields, '', '&');
        }
      }
      else
      {
        $options[CURLOPT_POST] = 1;
      }
    }
    
    curl_setopt_array($ch, $options);
    
    // grab URL and pass it to the browser
    $responseContent = curl_exec($ch);
    
    if (false === $responseContent)
    {
      throw new RuntimeException(sprintf("Failed request : %s", $url));
    }
    
    $responseInfos   = curl_getinfo($ch);
    
    // close cURL resource, and free up system resources
    curl_close($ch);

    $response = new Http_Response();
    $response->setInfos($responseInfos);
    $response->setContent($responseContent);
    $response->setHeaders($this->headers);

    $this->headers = array();
    
    return $response;
  }
  
  /**
   * @param bool $force
   * @return void
   */
  private function initCookieJar($force = false)
  {
    if (!$this->cookieJar || $force)
    {
      $this->cookieJar = tempnam ("/tmp", "CURLCOOKIE");
    }
  }
  
  /**
   * Get the cookie jar file path
   * @return string|null
   */
  public function getCookieJar()
  {
    return $this->cookieJar;
  }
  
  /**
   * We clear the cookie file at the end of the object life
   */
  public function __destruct()
  {
    if (is_file($this->cookieJar))
    {
      unlink($this->cookieJar);
    }
  }

  /**
   * Callback to allow CURL to get HTTP headers
   * @param $curl
   * @param $headers array
   * @return int
   */
  protected function readHeader($curl, $headers)
  {
    $this->headers[] = trim($headers);
    
    return strlen($headers);
  }
}
