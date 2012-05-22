<?php

class Output_Aggregated implements OutputInterface
{
  /**
   * @var array
   */
  public $outputs = array();

  /**
   * @param OutputInterface $output
   * @return void
   */
  public function addOutput(OutputInterface $output)
  {
    $this->outputs[] = $output;
  }

  /**
   * @param string $message
   * @param string $scope
   * @return bool
   */
  public function write($message, $scope = Output::SCOPE_PRIVATE)
  {
    foreach($this->outputs as $output)
    {
      $output->write($message, $scope);
    }

    return true;
  }
}