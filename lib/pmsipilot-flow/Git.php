<?php

/**
 * @todo refactor the shell exec system out of this
 */
class Git
{
  const B_REMOTE = 1;
  const B_ALL    = 2;

  const RESET_HARD = 1;

  /**
   * @var string
   */
  public $gitDir;

  /**
   * @var array
   */
  public $config = array();

  /**
   * @var array[]string
   */
  public $options = array(
    'debug' => false,
  );
  
  /**
   * @var array[]string
   */
  public $lastOutput = array();
  
  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @var Pflow_HistoryInterface
   */
  public $history;
  
  /**
   * @var Process_Runtime
   */
  private $runtime;

  /**
   * @throws InvalidArgumentException
   * @param $gitDir
   * @param Process_Runtime $runtime
   * @param OutputInterface $output
   * @param Pflow_HistoryInterface $history
   * @param array $options
   */
  public function __construct($gitDir, Process_Runtime $runtime, OutputInterface $output, Pflow_HistoryInterface $history, $options = array())
  {
    if (!file_exists($gitDir))
    {
      throw new InvalidArgumentException(sprintf('Could not find git directory "%s"', $gitDir));
    }

    $this->runtime = $runtime;
    $this->gitDir  = $gitDir;
    $this->output  = $output;
    $this->history = $history;
    $this->options = array_merge($this->options, $options);
  }

  /**
   * @return Pflow_HistoryInterface
   */
  public function getHistory()
  {
    return $this->history;
  }

  /**
   * @return OutputInterface
   */
  public function getOutput()
  {
    return $this->output;
  }

  /**
   * @param string $message
   * @param string $scope
   * @return true
   */
  public function output($message, $scope = Output::SCOPE_PRIVATE)
  {
    return $this->output->write($message, $scope);
  }

  /**
   * @param $name
   * @param $value
   * @return bool
   */
  public function setConfig($name, $value)
  {
    $command = 'config -f %s/config %s %s';
    $command = sprintf($command, $this->gitDir, escapeshellarg($name), escapeshellarg($value));

    $proc = $this->execute($command);
    
    if ($proc->isSuccess())
    {
      $this->config[$name] = $value;
      return true;
    }

    return false;
  }

  /**
   * @throws InvalidArgumentException
   * @param string $name
   * @return string
   */
  public function getConfig($name)
  {
    if (isset($this->config[$name]))
    {
      return $this->config[$name];
    }
    
    $command = sprintf('config -f %s/config --get %s', $this->gitDir, escapeshellarg($name));
    $proc    = $this->execute($command);
    $output  = $proc->getOutput();

    if ($proc->isSuccess())
    {
      return $output[0];
    }

    throw new InvalidArgumentException(sprintf('Could not find git configuration key "%s"', $name));
  }

  /**
   * @param string $name
   * @return bool
   */
  public function removeConfigSection($name)
  {
    return $this->execute(sprintf('config -f %s/config --remove-section %s', $this->gitDir, $name))->isSuccess();
  }

  /**
   * @param string $name
   * @return bool
   */
  public function removeConfig($name)
  {
    return $this->execute(sprintf('config -f %s/config --unset %s', $this->gitDir, $name))->isSuccess();
  }

  /**
   * @param int $flags
   * @param array $definition
   * @return array
   */
  public function parseFlags($flags, $definition)
  {
    $options = array();

    if (is_null($flags))
    {
      return $options;
    }

    foreach($definition as $flag => $option)
    {
      if ($flags & $flag)
      {
        $options[] = $option;
      }
    }

    return $options;
  }

  /**
   * @param int $flags
   * @return bool
   */
  public function reset($flags = null)
  {
    $options = $this->parseFlags($flags, array(
      self::RESET_HARD => '--hard',
    ));

    return $this->execute(sprintf('reset %s', implode(' ', $options)))->isSuccess();
  }

  /**
   * @return bool
   */
  public function hasDiff()
  {
    return !$this->execute('diff --exit-code > /dev/null')->isSuccess();
  }

  /**
   * @return bool
   */
  public function hasStagedDiff()
  {
    return !$this->execute('diff --exit-code --cached > /dev/null')->isSuccess();
  }

  /**
   * @return bool
   */
  public function isRebaseActive()
  {
    return is_dir($this->gitDir.'/rebase-apply/');
  }

  /**
   * @param string $upstream
   * @param string $target
   * @return bool
   */
  public function merge($upstream, $target)
  {
    $this->checkout($target);
    
    return $this->execute('merge '.$upstream)->isSuccess();
  }

  /**
   * @param string $remote
   * @param string $branch
   * @return bool
   */
  public function push($remote, $branch)
  {
    return $this->execute(sprintf('push %s %s', $remote, $branch))->isSuccess();
  }

  /**
   * @return bool
   */
  public function pull()
  {
    return $this->execute('pull')->isSuccess();
  }

  /**
   * @param string $upstream
   * @param string $target
   * @return bool
   */
  public function rebase($upstream, $target)
  {
    return $this->execute(sprintf('rebase %s %s', $upstream, $target))->isSuccess();
  }

  /**
   * @return bool
   */
  public function continueRebase()
  {
    return $this->execute('rebase --continue')->isSuccess();
  }

  /**
   * @return bool
   */
  public function abortRebase()
  {
    return $this->execute('rebase --abort')->isSuccess();
  }

  /**
   * @return bool
   */
  public function isMergeActive()
  {
    return is_file($this->gitDir.'/MERGE_HEAD');
  }

  /**
   * @return bool
   */
  public function commitMerge()
  {
    return $this->execute(sprintf('commit -F %s/COMMIT_EDITMSG', $this->gitDir))->isSuccess();
  }

  /**
   * @return bool
   */
  public function abortMerge()
  {
    return $this->execute('reset --merge')->isSuccess();
  }

  /**
   * @return string
   */
  public function getCurrentBranch()
  {
    $proc   = $this->execute('symbolic-ref HEAD');
    $output = $proc->getOutput();

    return str_replace('refs/heads/', '', $output[0]);
  }

  /**
   * @param null $flags
   * @return array
   */
  public function getBranches($flags = null)
  {
    $options = $this->parseFlags($flags, array(
      self::B_REMOTE => '-r',
      self::B_ALL    => '-a',
    ));

    $proc = $this->execute('branch '.implode(' ', $options));

    return $proc->getOutput();
  }

  /**
   * @param string $branchName
   * @return bool
   */
  public function hasRemoteBranch($branchName)
  {
    return $this->hasBranch('remotes/'.$branchName);
  }

  /**
   * @param string $branchName
   * @return bool
   */
  public function hasBranch($branchName)
  {
    foreach($this->getBranches(self::B_ALL) as $branch)
    {
      $branch = trim($branch, '* ');

      if ($branchName == $branch)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string $branch
   * @param string $tracking
   * @return int
   */
  public function createBranch($branch, $tracking = null)
  {
    return $this->execute(sprintf('branch %s %s', $branch, $tracking))->isSuccess();
  }

  /**
   * @param string $branch
   * @return integer
   */
  public function checkout($branch)
  {
    return $this->execute('checkout '.$branch)->isSuccess();
  }

  /**
   * @param string $remote
   * @return integer
   */
  public function fetch($remote = null)
  {
    return $this->execute(sprintf('fetch %s', $remote))->isSuccess();
  }

  /**
   * @param string $upstream
   * @param string $head
   * @return array
   */
  public function cherry($upstream, $head = 'HEAD')
  {
    $proc = $this->execute(sprintf('cherry %s %s', $upstream, $head));

    return $proc->getOutput();
  }

  /**
   * @return bool
   */
  public function isBehind()
  {
    $currentBranch = $this->getCurrentBranch();

    return count($this->cherry($currentBranch, 'origin/'.$currentBranch)) > 0;
  }

  /**
   * @return bool
   */
  public function isAhead()
  {
    $currentBranch = $this->getCurrentBranch();

    return count($this->cherry('origin/'.$currentBranch)) > 0;
  }

  /**
   * @param $remote
   * @param null $remoteBranch
   * @return bool
   */
  public function updateBranchFromRemote($remote, $remoteBranch = null)
  {
    $remoteBranch = is_null($remoteBranch) ? $this->getCurrentBranch() : $remoteBranch;
    $this->fetch($remote);
    return $this->execute(sprintf('merge %s/%s', $remote, $remoteBranch))->isSuccess();
  }
  
  /**
   * @param $branch
   * @return array
   */
  public function getLastCommit($branch)
  {
    $proc = $this->execute(sprintf('rev-parse --verify %s', $branch));

    $output = $proc->getOutput();
    
    return $output[0];
  }

  /**
   * @param $command
   * @param array $options
   * @return Process
   */
  public function execute($command, $options = array())
  {
    $proc   = $this->runtime->execute($command, $options);
    $output = $proc->getOutput();
    $error  = $proc->getError();

    if (!$proc->isSuccess() || $this->options['debug'])
    {
      $this->output(sprintf('Last command exit status: %d', $proc->getExitCode()), Output::SCOPE_DEBUG);
      $this->output(implode(PHP_EOL, $output), Output::SCOPE_STANDARD_STREAMS);
      $this->output(implode(PHP_EOL, $error), Output::SCOPE_STANDARD_STREAMS);
    }

    return $proc;
  }
}
