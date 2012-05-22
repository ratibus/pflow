<?php

class Pflow_HookLauncher
{
  /**
   * @var Pflow_Hook
   */
  protected $hook;
  
  /**
   * @param Pflow_Command $command
   * @param OutputInterface $output
   */
  public function __construct(Pflow_Command $command, OutputInterface $output)
  {
    $hookClass = str_replace('Pflow_Command', 'Pflow_Hook', get_class($command));
    if (class_exists($hookClass))
    {
      $this->hook = new $hookClass($command, $output);
    }
  }

  /**
   * Run pre-hook
   */
  public function executePre()
  {
    if ($this->hook)
    {
      $this->hook->executePre();
    }
  }

  /**
   * Run post-hook
   */
  public function executePost()
  {
    if ($this->hook)
    {
      $this->hook->executePost();
    }
  }
}
