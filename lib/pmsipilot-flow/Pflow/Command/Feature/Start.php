<?php

class Pflow_Command_Feature_Start extends Pflow_Command_Feature
{
  /**
   * @throws InvalidArgumentException
   * @param array $argv
   * @return integer
   */
  public function execute($argv)
  {
    $options = pflow_getopt($argv, 'db:np');
    
    // update repo
    $this->git->fetch('origin');

    // create feature branch
    $branch = $this->getFeatureName($argv);

    // if we have a local branch, just switch to it
    if ($this->git->hasBranch($branch))
    {
      $this->output(sprintf('Feature branch "%s" exists, switching to it', $branch));
      return $this->git->checkout($branch);
    }

    // decide which branch to branch from
    $baseBranch = $this->getBaseBranch($options, $this->getConfig('pflow.base'));

    // and switch to it
    if (false === $this->git->checkout($baseBranch))
    {
      $this->output('Could not checkout to base branch. Commit or stash changes, then run "pflow continue"');

      return 0;
    }

    // run the steps!
    $steps = array(
      'updateBaseBranch'    => array($options, $baseBranch),
      'createFeatureBranch' => array($options, $branch, $baseBranch),
      'output'              => array(sprintf('Branch (%s) created', $branch)),
      'createDatabase'      => array($options, $branch),
      'showSummary'         => array($baseBranch, $branch),
    );

    $runner = $this->getStepsRunner($steps);

    if ($runner->run($this->isContinued() ? $this->getConfig('pflow.continue') : null))
    {
      $this->outputSuccessfulStart($argv, $baseBranch);
      return 1;
    }

    return 0;
  }
  
  /**
   * @param $argv
   * @param $baseBranch
   */
  protected function outputSuccessfulStart($argv, $baseBranch)
  {
    $this->output(sprintf('Started working on feature "%s" (branched from %s)', $this->getFeatureName($argv), $baseBranch), Output::SCOPE_PUBLIC);
  }

  /**
   * @throws InvalidArgumentException
   * @param $options
   * @param $default
   * @return 
   */
  public function getBaseBranch($options, $default)
  {
    // choose the head to branch from
    $baseBranch = $default;
    
    if (isset($options['b']))
    {
      if (!$this->git->hasBranch($options['b']))
      {
        if ($this->git->hasRemoteBranch('origin/'.$options['b']))
        {
          $this->output(sprintf('No matching "%1$s" local branch, but found "origin/%1$s" instead. Creating local "%1$s" branch prior to feature branching', $options['b']));
          $this->git->createBranch($options['b'], 'origin/'.$options['b']);
        }
        else
        {
          throw new InvalidArgumentException(sprintf('Unable to branch from inexistent branch "%s"', $options['b']));
        }
      }

      $baseBranch = $options['b'];
    }

    return $baseBranch;
  }

  /**
   * @param $options
   * @param $baseBranch
   * @return bool
   */
  public function updateBaseBranch($options, $baseBranch)
  {
    // possibly update the base branch before branching (unless -n is set)
    if (!isset($options['n']) && $this->git->hasRemoteBranch('origin/'.$baseBranch))
    {
      if ($this->git->isMergeActive())
      {
        $this->output('Committing merge');
        $this->git->commitMerge();
      }
      else
      {
        $this->output(sprintf('Trying to update base branch (%s) from remote origin', $baseBranch));
        
        if (!$this->git->updateBranchFromRemote('origin'))
        {
          $this->output('Base branch merge failed. Resolve the conflicts, then run "pflow continue".');
          $this->git->setConfig('pflow.continue', $this->getStepsRunner()->getCurrentStep());

          return false;
        }
      }
    }
  }

  /**
   * @param $options
   * @param $branch
   * @param $baseBranch
   * @return void
   */
  public function createFeatureBranch($options, $branch, $baseBranch)
  {
    $this->output(sprintf('Checking out branch (%s) from base branch (%s)', $branch, $baseBranch));
    
    $this->git->createBranch($branch, $this->git->hasRemoteBranch('origin/'.$branch) ? 'origin/'.$branch : null);
    $this->git->checkout($branch);
    $this->git->setConfig(sprintf('branch.%s.base', $branch), $baseBranch);

    // push the branch if -p is set
    if (isset($options['p']) && !$this->git->hasRemoteBranch('origin/'.$branch))
    {
      $this->output(sprintf('Pushing feature branch (%s) to remote origin', $branch));
      $this->git->push('origin', $branch);
      $this->git->setConfig(sprintf('branch.%s.remote', $branch), 'origin');
      $this->git->setConfig(sprintf('branch.%s.merge', $branch), 'refs/heads/'.$branch);
    }
  }

  /**
   * @param $baseBranch
   * @param $branch
   * @return void
   */
  public function showSummary($baseBranch, $branch)
  {
    if ($this->git->hasRemoteBranch('origin/'.$branch))
    {
      $startPoint = 'origin/'.$branch;
    }
    else
    {
      $startPoint = $baseBranch;
    }
    
    $this->output(sprintf('Base branch: %s ', $baseBranch));
    $this->output(sprintf('Start point: %s', $startPoint));
    $this->output(sprintf('Started working on feature "%s"', $branch));
  }
  
  /**
   * @param array $options
   * @param string $branch
   * @return void
   */
  public function createDatabase($options, $branch)
  {
    // create database if necessary
    if (isset($options['d']) && $this->getConfig('pflow.db.enabled'))
    {
      $database_name = strtr($this->getConfig('pflow.db.template'), array(
        '%branch%' => $branch,
      ));

      $this->output(sprintf('Creating database (%s)', $database_name));

      exec(sprintf('mysql -u %s %s -e "CREATE DATABASE IF NOT EXISTS %s DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;"',
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
      'description' => "start a feature branch off the base branch",
      'arguments'   => array('feature_name'),
      'options'     => array(
        'b:branch' => "base branch from feature rather than the default base branch",
        'd'        => "create database for feature branch",
        'n'        => "do not update the base branch before branching",
        'p'        => "automatically push the branch after creation",
      ),
    );
  }
}