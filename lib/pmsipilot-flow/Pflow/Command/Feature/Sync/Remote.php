<?php

class Pflow_Command_Feature_Sync_Remote extends Pflow_Command_Feature
{
  /**
   * @param array $argv
   * @return int
   */
  public function execute($argv)
  {
    $currentBranch = $this->git->getCurrentBranch();
    
    if (!$this->isContinued())
    {
      $this->output(sprintf('Trying to merge current branch (%s) from remote origin', $currentBranch));

      if (!$this->git->pull())
      {
        $this->output('Pull failed. Resolve the conflicts, then run "pflow continue"');
      }

      return 1;
    }

    return 0;
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "synchronizes feature branch with its remote branch",
    );
  }
}