<?php

class Pflow_Command_Log_Purge extends Pflow_Command
{
  /**
   * @param $argv
   * @return bool
   */
  public function execute($argv)
  {
    return $this->git->getHistory()->purge() ? 1 : 0;
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "purges the log in .git/pflow/history",
    );
  }
}