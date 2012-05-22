<?php

class Pflow_Command_Feature_Sync extends Pflow_Command_Feature
{
  /**
   * @param array $argv
   * @return int
   */
  public function execute($argv)
  {
    $steps = array(
      'syncRemote' => null,
      'syncBase'   => null,
    );

    $stepRunner = $this->getStepsRunner($steps);

    if ($this->isContinued())
    {
      $continue = explode('.', $this->getConfig('pflow.continue'));
      $this->git->setConfig('pflow.continue', $continue[1]);
    }
    
    if (0 == $stepRunner->run(isset($continue) ? $continue[0] : null) ? 0 : 1)
    {
      $this->output('Feature branch synchronized');
    }

    return 0;
  }

  /**
   * @return bool
   */
  public function syncRemote()
  {
    if (0 !== $this->runCommand('feature sync remote'))
    {
      $this->git->setConfig('pflow.continue', sprintf('%s.%s', $this->getStepsRunner()->getCurrentStep(), $this->git->getConfig('pflow.continue')));
      return false;
    }
  }

  /**
   * @return bool
   */
  public function syncBase()
  {
    if (0 !== $this->runCommand('feature sync base'))
    {
      $this->git->setConfig('pflow.continue', sprintf('%s.%s', $this->getStepsRunner()->getCurrentStep(), $this->git->getConfig('pflow.continue')));
      return false;
    }
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "synchronizes feature branch with base branch and remote branch",
    );
  }
}