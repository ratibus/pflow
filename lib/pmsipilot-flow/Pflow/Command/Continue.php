<?php

class Pflow_Command_Continue extends Pflow_Command
{
  /**
   * @param string $argv
   * @return int
   */
  public function execute($argv)
  {
    $lastCommand = $this->history->getLast();
    $ret         = $this->runCommand($lastCommand);

    if ($ret == 0)
    {
      $this->git->removeConfig('pflow.continue');
    }

    return $ret;
  }

  /**
   * @return Pflow_Dispatcher
   */
  public function getDispatcher()
  {
    return new Pflow_Dispatcher($this->git, $this->output, $this->input, $this->history);
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "tries to continue the last interrupted action",
    );
  }
}