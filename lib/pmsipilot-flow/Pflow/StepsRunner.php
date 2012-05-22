<?php

class Pflow_StepsRunner
{
  /**
   * @var object
   */
  public $subject;
  
  /**
   * @var array
   */
  public $steps = array();

  /**
   * @var string
   */
  public $currentStep;

  /**
   * @var OutputInterface
   */
  public $output;

  /**
   * @param $subject
   * @param array $steps
   * @param OutputInterface $output
   */
  public function __construct($subject, array $steps, OutputInterface $output)
  {
    $this->subject = $subject;
    $this->steps   = $steps;
    $this->output  = $output;
  }

  /**
   * @param string $message
   * @param string $scope
   * @return mixed
   */
  private function output($message, $scope = Output::SCOPE_PRIVATE)
  {
    return $this->output->write($message, $scope);
  }

  /**
   * @return string
   */
  public function getCurrentStep()
  {
    return $this->currentStep;
  }
  
  /**
   * @param null $current
   * @return mixed
   */
  public function getNextStep($current = null)
  {
    if (is_null($current))
    {
      // avoid messing with the steps array pointer
      $steps = $this->steps;
      next($steps);
      return key($steps);
    }

    $steps_keys    = array_keys($this->steps);
    $current_index = array_search($current, $steps_keys);

    return $steps_keys[$current_index + 1];
  }

  /**
   * @param string $start
   * @return bool
   */
  public function run($start = null)
  {
    if (!is_null($start))
    {
      while (key($this->steps) != $start)
      {
        next($this->steps);
      }

      $this->output(sprintf('Running from step "%s"', $start), Output::SCOPE_DEBUG);
    }

    // we use a while here instead of a foreach
    // because we want to take the actual array pointer into account
    while(list($step, $args) = each($this->steps))
    {
      $this->currentStep = $step;

      $this->output(sprintf('running step "%s"', $step), Output::SCOPE_DEBUG);

      if (!is_callable(array($this->subject, $step)))
      {
        throw new RuntimeException(sprintf('Could not execute step %s#%s', get_class($this->subject), $step));
      }
      
      if (false === call_user_func_array(array($this->subject, $step), $args))
      {
        $this->output(sprintf('Step "%s" failed', $step), Output::SCOPE_DEBUG);
        return false;
      }
    }
    
    return true;
  }
}