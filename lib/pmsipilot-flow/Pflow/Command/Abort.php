<?php

class Pflow_Command_Abort extends Pflow_Command
{
  /**
   * @param $argv
   * @return int
   */
  public function execute($argv)
  {
    if ($this->git->isRebaseActive())
    {
      $this->output('Aborting rebase');
      $this->git->abortRebase();
    }
    else if ($this->git->isMergeActive())
    {
      if ('yes' == $this->input('this will "git reset --hard" and lose any uncommited changes, are you sure ?', 'no', array('yes', 'no')))
      {
        $this->git->reset(Git::RESET_HARD);
      }
    }
    
    $this->runCommand('continue reset');

    return 1;
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "tries to abort the last interrupted action",
    );
  }
}