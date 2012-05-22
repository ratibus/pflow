<?php

/**
 *
 */
class Output_Console extends Output
{
  /**
   * @var array
   */
  public $scopesWhitelist = array();

  /**
   * @param array $scopesWhitelist
   */
  public function __construct($scopesWhitelist = array())
  {
    $this->scopesWhitelist = $scopesWhitelist;

    $this->initialize();
  }

  public function initialize()
  {
    $this->scopesWhitelist = array(Output::SCOPE_PRIVATE);
  }

  public function acceptScope($scope)
  {
    return empty($this->scopesWhitelist) || in_array($scope, $this->scopesWhitelist);
  }
  
  /**
   * @param string $message
   * @param string $scope
   * @return void
   */
  public function write($message, $scope = Output::SCOPE_PRIVATE)
  {
    if ($this->acceptScope($scope))
    {
      if (strpos($message, "\033") !== false)
      {
        echo $message.PHP_EOL;
      }
      else
      {
        echo "\033[1;35m".$message."\033[0m".PHP_EOL;
      }
    }
  }
}