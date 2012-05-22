<?php

class Output_StandardStreams extends Output_Console
{
  /**
   * @return void
   */
  public function initialize()
  {
    $this->scopesWhitelist = array(Output::SCOPE_STANDARD_STREAMS);
  }

  /**
   * @param $message
   * @param string $scope
   */
  public function write($message, $scope = Output::SCOPE_PRIVATE)
  {
    if ($this->acceptScope($scope))
    {
      echo "\033[36m".$message."\033[0m".PHP_EOL;
    }
  }
}