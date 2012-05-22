<?php

class Pflow_Command_Feature_Finish extends Pflow_Command_Feature
{
  /**
   * pflow feature finish
   *
   * @param array $argv
   * @return integer
   */
  public function execute($argv)
  {
    $options = pflow_getopt($argv, 'pd');

    if ($this->isContinued())
    {
      $featureBranch = $this->getConfig('pflow.finish-feature');

      if (false === $this->git->checkout($featureBranch))
      {
        $this->output(sprintf('Could not checkout to feature branch %s.', $featureBranch));

        return 1;
      }
    }
    else
    {
      $featureBranch = $this->git->getCurrentBranch();
      $this->git->setConfig('pflow.finish-feature', $featureBranch);
    }

    try
    {
      $baseBranch = $this->getConfig(sprintf('branch.%s.base', $featureBranch));
    }
    catch (InvalidArgumentException $e)
    {
      $this->output(sprintf('Unable to find base branch configuration for feature branch %s.', $featureBranch));
  
      return 1;
    }

    $steps = array(
      'updateBaseBranch'       => array($baseBranch),
      'tryMergeFeatureBranch'  => array($featureBranch, $baseBranch),
      'pushFeatureBranch'      => array($options, $baseBranch),
      'dropDatabase'          => array($options, $featureBranch),
    );

    $runner = $this->getStepsRunner($steps);

    if (0 == $runner->run($this->isContinued() ? $this->getConfig('pflow.continue') : null) ? 0 : 1)
    {
      $this->git->removeConfig('pflow.finish-feature');
      $this->output(sprintf('Finished working on feature %s (merged in %s)', $featureBranch, $baseBranch), Output::SCOPE_PUBLIC);
      return 0;
    }

    return 1;
  }

  /**
   * @param string $baseBranch
   * @return void
   */
  public function updateBaseBranch($baseBranch)
  {
    $this->output(sprintf('Updating base branch (%s)', $baseBranch));
    // first, fast-forward the base branch
    // (this should never fail)
    // @todo add a check in case it fails anyway
    $this->git->fetch('origin');

    if ($this->git->hasRemoteBranch('origin/'.$baseBranch))
    {
      $this->git->merge('origin/'.$baseBranch, $baseBranch);
    }
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
    
    if ($this->git->isMergeActive())
    {
      $this->output('Committing merge');
      return $this->git->commitMerge();
    }
    else
    {
      $this->output(sprintf('Trying to merge feature branch (%s) into base branch (%s)', $featureBranch, $baseBranch));

      if (!$this->git->merge($featureBranch, $baseBranch))
      {
        $this->output('Merge failed. Resolve the conflicts, then run "pflow continue"');
        $this->git->setConfig('pflow.continue', $this->getStepsRunner()->getCurrentStep());
        return false;
      }
    }
  }

  /**
   * @param array $options
   * @param string $baseBranch
   * @return int
   */
  public function pushFeatureBranch($options, $baseBranch)
  {
    // push if need be
    if (isset($options['p']))
    {
      $this->output(sprintf('Pushing base branch (%s) to remote origin', $baseBranch));
      return $this->git->push('origin', $baseBranch);
    }
  }

  /**
   * @param array $options
   * @param string $branch
   */
  public function dropDatabase($options, $branch)
  {
    if (isset($options['d']) && $this->getConfig('pflow.db.enabled'))
    {
      $database_name = strtr($this->getConfig('pflow.db.template'), array(
        '%branch%' => $branch,
      ));

      $this->output(sprintf('Dropping database (%s)', $database_name));

      exec(sprintf('mysql -u %s %s -e "DROP DATABASE IF EXISTS %s;"',
        $this->getConfig('pflow.db.user'),
        $this->getConfig('pflow.db.use-password') ? '-p' : '',
        $database_name
      ));
    }
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "merges a feature branch in the base branch",
      'options'     => array(
        'p' => "push the changes after merging",
        'd' => "drop database for feature branch",
      ),
    );
  }
  
}