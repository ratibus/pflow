<?php

class Pflow_Hook_Feature extends Pflow_Hook
{
  /**
   * Features hooks provide the following parameters:
   *   - feature branch name
   *   - base branch name
   * 
   * @return array
   */
  protected function getParameters()
  {
    $featureBranch = $this->command->git->getCurrentBranch();
    
    try
    {
      $baseBranch = $this->command->getConfig(sprintf('branch.%s.base', $featureBranch));
    }
    catch (InvalidArgumentException $e)
    {
      throw new RuntimeException(sprintf('Unable to find base branch configuration for feature branch %s.', $featureBranch));
    }
    
    return array($featureBranch, $baseBranch);
  }
}
