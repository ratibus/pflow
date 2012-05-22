<?php

class Pflow_Hook
{
  /**
   * @var Pflow_Command
   */
  protected $command;
  
  /**
   * @var OutputInterface
   */
  protected $output;

  /**
   * @param Pflow_Command $command
   * @param OutputInterface $output
   */
  public function __construct(Pflow_Command $command, OutputInterface $output)
  {
    $this->command = $command;
    $this->output  = $output;
  }

  /**
   * Execute pre-hook
   */
  public function executePre()
  {
    $this->executeHook('pre');
  }

  /**
   * Execute post-hook
   */
  public function executePost()
  {
    $this->executeHook('post');
  }
  
  /**
   * Run pre/post hook
   * 
   * @param string $hookType
   * @throws RuntimeException
   */
  protected function executeHook($hookType)
  {
    $commandScript = sprintf('%s/pflow/hooks/%s-%s', $this->command->git->gitDir, $hookType, $this->command->getName());
    
    if (is_file($commandScript))
    {
      if (is_executable($commandScript))
      {
        $command = escapeshellcmd($commandScript) . ' ' . implode(' ', array_map('escapeshellarg', $this->getParameters()));

        $this->output(sprintf('Running %s-hooks', $hookType));
        $this->output("  $command");

        exec($command, $commandOutput, $commandStatus);

        $this->output(implode(PHP_EOL, $commandOutput));
        if ($commandStatus !== 0)
        {
          throw new RuntimeException(sprintf("Hook %s failed (return code: %d)", basename($commandScript), $commandStatus));
        }
      }
      else
      {
        $this->output(sprintf('%s hook file is not executable. We did not run it.', basename($commandScript)));
      }
    }
  }
  
  /**
   * @return array
   */
  protected function getParameters()
  {
    return array();
  }

  /**
   * @param string $message
   * @param string $scope
   * @return bool
   */
  protected function output($message, $scope = Output::SCOPE_PRIVATE)
  {
    return $this->output->write($message, $scope);
  }
  
}
