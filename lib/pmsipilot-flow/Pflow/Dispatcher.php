<?php

class Pflow_Dispatcher
{
  /**
   * @var Git
   */
  public $git;

  /**
   * @var InputInterface
   */
  public $input;

  /**
   * @var Pflow_HistoryInterface
   */
  public $history;

  /**
   * @param Git $git
   * @param OutputInterface $output
   * @param InputInterface $input
   * @param Pflow_HistoryInterface $history
   */
  public function __construct(Git $git, OutputInterface $output, InputInterface $input, Pflow_HistoryInterface $history)
  {
    $this->git     = $git;
    $this->output  = $output;
    $this->input   = $input;
    $this->history = $history;
  }

  /**
   * @param array $argv
   * @return Pflow_CommandInterface
   * @throws BadMethodCallException
   */
  public function dispatch(array $argv)
  {
    for ($i = count($argv); $i > 1; $i--)
    {
      $command = sprintf('Pflow_Command_%s', implode('_', array_map('ucfirst', array_slice($argv, 1, $i - 1))));

      if (class_exists($command))
      {
        $reflectionClass = new ReflectionClass($command);
        if ($reflectionClass->IsInstantiable() && $reflectionClass->implementsInterface('Pflow_CommandInterface'))
        {
          return new $command($this->git, $this->output, $this->input, $this->history);
        }
      }
    }

    throw new BadMethodCallException(sprintf('Could not find command in "%s"', implode(' ', $argv)));
  }
}