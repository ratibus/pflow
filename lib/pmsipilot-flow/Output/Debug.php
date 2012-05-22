<?php

/**
 * 
 */
class Output_Debug extends Output_Console
{
  /**
   * @var bool
   */
  private $enabled = false;

  /**
   * @param bool $enabled
   */
  public function __construct($enabled = false)
  {
    $this->enabled = $enabled;

    parent::__construct();
  }

  /**
   * @param string $scope
   * @return bool
   */
  public function acceptScope($scope)
  {
    return $this->enabled ? parent::acceptScope($scope) : false;
  }
  
  /**
   * @return void
   */
  public function initialize()
  {
    $this->scopesWhitelist = array(Output::SCOPE_DEBUG);
  }
}