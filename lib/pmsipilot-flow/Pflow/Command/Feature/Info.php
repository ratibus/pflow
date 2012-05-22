<?php

class Pflow_Command_Feature_Info extends Pflow_Command_Feature
{
  /**
   * @param array $argv
   * @return int
   */
  public function execute($argv)
  {
    $currentBranch      = $this->git->getCurrentBranch();

    try
    {
      $baseBranch = $this->getConfig(sprintf('branch.%s.base', $currentBranch));
      $this->output(sprintf('Base branch:       %s', $baseBranch));
    }
    catch (InvalidArgumentException $e)
    {
      $this->output(sprintf('Unable to find base branch configuration for feature branch %s.', $currentBranch));
      
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
      'description'           => "show some informations about the feature",
    );
  }
}