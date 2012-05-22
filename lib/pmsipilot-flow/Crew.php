<?php

class Crew
{
  /**
   * @var string Crew server base URL
   */
  private $url;
  
  /**
   * @param string $url
   */
  public function __construct($url)
  {
    $this->setUrl($url);
  }

  /**
   * @param $url
   * @return Crew
   */
  public function setUrl($url)
  {
    $this->url = $url;
    return $this;
  }

  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
  
  /**
   * @return mixed
   */
  public function getProjects()
  {
    $client = new Http_Client();
    $url    = sprintf('%s/api.php/projects', $this->getUrl());
    $response = $client->get($url);
    
    if ($response->getCode() == 200)
    {
      return json_decode($response->getContent());
    }
    else
    {
      throw new RuntimeException(sprintf("URL %s returned %s status code", $url, $response->getCode()));
    }
  }
  
  /**
   * @throws RuntimeException
   * @param int $projectId
   * @param string $branch
   * @param string $baseBranch
   * @param string $lastCommit
   * @return string
   */
  public function reviewRequest($projectId, $branch, $baseBranch, $lastCommit)
  {
    $client   = new Http_Client();
    $params = array(
      'branch'      => $branch,
      'base_branch' => $baseBranch,
      'commit'      => $lastCommit,
    );
    $response = $client->post(sprintf('%s/api.php/projects/%s/reviews', $this->getUrl(), $projectId), $params);
    $json = (array)json_decode($response->getContent());
    
    if (!in_array($response->getCode(), array(200, 201)) || is_null($json))
    {
      if (isset($json['message']))
      {
        throw new RuntimeException(sprintf('Crew->reviewRequest : %s', $json['message']));
      }
      throw new RuntimeException(sprintf("Crew->reviewRequest : invalid result data.\n\n%s", $response->getContent()));
    }

    return $json['message'];
  }
  
  /**
   * @throws RuntimeException
   * @param $projectId
   * @param $branch
   * @return array
   */
  public function reviewStatus($projectId, $branch)
  {
    $client   = new Http_Client();

    $response = $client->get(sprintf('%s/api.php/projects/%s/reviews/%s', $this->getUrl(), $projectId, $branch));
    $json = (array)json_decode($response->getContent());

    if ($response->getCode() != 200 || $json === null || count($json) == 0)
    {
      if (isset($json['message']))
      {
        throw new RuntimeException(sprintf('Crew->reviewStatus : %s', $json['message']));
      }
      throw new RuntimeException(sprintf("Crew->reviewStatus : invalid result data.\n\n%s", $response->getContent()));
    }

    return $json;
  }
}
