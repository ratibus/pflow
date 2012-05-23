<?php

class Pflow_Command_Feature_Sync_Base extends Pflow_Command_Feature
{
  /**
   * @param array $argv
   * @return int
   */
  public function execute($argv)
  {
    try
    {
      $featureBranch = $this->getConfig('pflow.merge-feature');
    }
    catch (InvalidArgumentException $e)
    {
      $featureBranch = $this->git->getCurrentBranch();
      $this->git->setConfig('pflow.merge-feature', $featureBranch);
    }

    try
    {
      $baseBranch = $this->getConfig(sprintf('branch.%s.base', $featureBranch));
    }
    catch (InvalidArgumentException $e)
    {
      $this->output(sprintf('Unable to find base branch configuration for feature branch %s.', $featureBranch));
  
      return 0;
    }

    $steps = array(
      'updateBaseBranch'      => array($featureBranch, $baseBranch),
      'tryMergeFeatureBranch' => array($featureBranch, $baseBranch)
    );

    $runner = $this->getStepsRunner($steps);

    if ($runner->run($this->isContinued() ? $this->getConfig('pflow.continue') : null))
    {
      $this->git->removeConfig('pflow.merge-feature');
      return 1;
    }

    return 0;
  }

  /**
   * @param string $featureBranch
   * @param string $baseBranch
   * @return void
   */
  public function updateBaseBranch($featureBranch, $baseBranch)
  {
    $this->output(sprintf('Updating base branch (%s)', $baseBranch));
    // first, fast-forward the base branch
    $this->git->fetch('origin');
    $this->git->checkout($baseBranch);

    if ($this->git->hasRemoteBranch('origin/'.$baseBranch))
    {
      $this->git->merge('origin/'.$baseBranch, $baseBranch);
    }
    
    $this->git->checkout($featureBranch);
  }

  /**
   * @param string $featureBranch
   * @param string $baseBranch
   * @return bool
   */
  public function tryMergeFeatureBranch($featureBranch, $baseBranch)
  {
    if ($this->git->hasDiff() || $this->git->hasStagedDiff())
    {
      $this->output('Changes to be committed detected, aborting. Commit or stash changes, then run "pflow continue"');
      $this->git->setConfig('pflow.continue', $this->getStepsRunner()->getCurrentStep());
      return false;
    }

    // we merge the base branch in the feature branch
    // to keep the feature branch up to date
    if ($this->git->isMergeActive())
    {
      $this->output('Committing merge');
      return $this->git->commitMerge();
    }
    else
    {
      $this->output(sprintf('Trying to merge base branch (%s) into feature branch (%s)', $baseBranch, $featureBranch));

      if (!$this->git->merge($baseBranch, $featureBranch))
      {
        $this->output('Merge failed. Resolve the conflicts, then run "pflow continue"');
        $this->git->setConfig('pflow.continue', $this->getStepsRunner()->getCurrentStep());
        return false;
      }
    }
  }
  
  public static function getHelp()
  {
    return array(
      'description' => "synchronizes feature branch with its base branch",
    );
  }
}