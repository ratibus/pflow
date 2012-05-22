<?php
 
class Http_Response
{
  /**
   * @var array
   */
  private $infos;
  
  /**
   * @var string
   */
  private $content;
  
  /**
   * @var array
   */
  private $headers = array();
  
  /**
   * 
   */
  public function __construct()
  {
  }

  /**
   * Setter
   * @param $content string
   * @return void
   */
  public function setContent($content)
  {
    $this->content = $content;
  }

  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * @param $infos array
   * @return void
   */
  public function setInfos($infos)
  {
    $this->infos = $infos;
  }

  /**
   * @return array
   */
  public function getInfos()
  {
    return $this->infos;
  }
  
  /**
   * Get a specific info from Response
   * @throws InvalidArgumentException
   * @param $name
   * @return 
   */
  private function getInfo($name)
  {
    if (array_key_exists($name, $this->infos))
    {
      return $this->infos[$name];
    }
    else
    {
      throw new InvalidArgumentException(sprintf("%s info is not available", $name));
    }
  }
  
  /**
   * @return string
   */
  public function getContentType()
  {
    return $this->getInfo('content_type');
  }
  
  /**
   * @return 
   */
  public function getCode()
  {
    return $this->getInfo('http_code');
  }
  
  /**
   * @return string
   */
  public function getCharset()
  {
    // TODO parse Content-Type to get the real charset
    return 'utf-8';
  }
  
  /**
   * @param $headers array
   * @return void
   */
  public function setHeaders($headers)
  {
    $this->headers = $headers;
  }

  /**
   * @return array
   */
  public function getHeaders()
  {
    return $this->headers;
  }

}
