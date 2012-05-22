<?php

/**
 * 
 */
abstract class Pflow_Command implements Pflow_CommandInterface
{
  /**
   * @var Git
   */
  public $git;

  /**
   * @var Pflow_CommandInterface
   */
  public $parent;

  /**
   * @var array
   */
  public $options = array();

  /**
   * @var Pflow_StepsRunner
   */
  private $stepsRunner;

  /**
   * @var Pflow_Dispatcher
   */
  private $dispatcher;

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

    $this->initialize();
  }

  /**
   * @param array $steps
   * @return Pflow_StepsRunner
   */
  public function getStepsRunner(array $steps = array())
  {
    if (!isset($this->stepsRunner))
    {
      if (empty($steps))
      {
        throw new InvalidArgumentException('First call to getStepsRunner must come with some steps');
      }

      $this->stepsRunner = new Pflow_StepsRunner($this, $steps, $this->git->getOutput());
    }

    return $this->stepsRunner;
  }

  /**
   * @return Pflow_Dispatcher
   */
  private function getDispatcher()
  {
    if (is_null($this->dispatcher))
    {
      $this->dispatcher = new Pflow_Dispatcher($this->git, $this->output, $this->input, $this->history);
    }

    return $this->dispatcher;
  }

  /**
   * @param string $command
   * @return void
   */
  public function runCommand($command)
  {
    $command = trim($command);
    $argv    = explode(' ', $command);
    array_unshift($argv, $GLOBALS['argv'][0]);
    
    $command = $this->getDispatcher()->dispatch($argv);
    $command->setParent($this);

    return $command->execute($argv);
  }

  /**
   * @param Pflow_CommandInterface $parent
   * @return void
   */
  public function setParent(Pflow_CommandInterface $parent)
  {
    $this->parent = $parent;
  }

  /**
   * @return Pflow_CommandInterface
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * @return bool
   */
  public function isContinued()
  {
    if (null === $parent = $this->getParent())
    {
      return false;
    }
    else
    {
      return $parent instanceof Pflow_Command_Continue || $parent->isContinued();
    }
  }

  /**
   * @param string $message
   * @param string $scope
   * @return bool
   */
  public function output($message, $scope = Output::SCOPE_PRIVATE)
  {
    return $this->output->write($message, $scope);
  }

  /**
   * @param $prompt
   * @param null $default
   * @param null $allowedValues
   * @return string
   */
  public function input($prompt, $default = null, $allowedValues = null)
  {
    return $this->input->readValue($prompt, $default, $allowedValues);
  }

  /**
   * @param string $name
   * @return string
   */
  public function getConfig($name)
  {
    return $this->git->getConfig($name);
  }

  /**
   * @return void
   */
  public function initialize()
  {
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return str_replace('_', '-', strtolower(str_replace('Pflow_Command_', '', get_class($this))));
  }
}