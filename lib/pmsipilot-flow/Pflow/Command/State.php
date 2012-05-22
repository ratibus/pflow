<?php

class Pflow_Command_State extends Pflow_Command
{
  /**
   * @param $argv
   */
  public function execute($argv)
  {
    if ($this->git->isRebaseActive())
    {
      $this->output('State:          In the middle of a rebase');
    }
    else if ($this->git->isMergeActive())
    {
      $this->output('State:          In the middle of a merge');
    }
    else
    {
      $this->output('State:          Clean');
    }

    $this->output('Last command:   '.$this->history->getLast());

    try
    {
      $this->output('Continue point: '.$this->getConfig('pflow.continue'));
    }
    catch (Exception $e)
    {
      $this->output('Continue point: not set');
    }

    try
    {
      $this->output('Finish-feature:  '.$this->getConfig('pflow.finish-feature'));
    }
    catch (Exception $e)
    {
      $this->output('Finish-feature:  not set');
    }
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "shows infos on the current state of your repository",
    );
  }
}