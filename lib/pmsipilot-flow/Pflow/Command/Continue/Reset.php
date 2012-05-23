<?php

class Pflow_Command_Continue_Reset extends Pflow_Command_Continue
{
  /**
   * @param $argv
   * @return int
   */
  public function execute($argv)
  {
    $this->git->removeConfig('pflow.continue');
    $this->git->removeConfig('pflow.finish-feature');

    return 1;
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "resets continue informations",
    );
  }
}