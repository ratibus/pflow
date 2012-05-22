<?php

class Pflow_Command_Feature_Publish extends Pflow_Command_Feature
{
  /**
   * @param array $argv
   * @return int
   */
  public function execute($argv)
  {
    $currentBranch = $this->git->getCurrentBranch();

    $this->git->push('origin', $currentBranch);

    $this->git->setConfig(sprintf('branch.%s.remote', $currentBranch), 'origin');
    $this->git->setConfig(sprintf('branch.%s.merge', $currentBranch), 'refs/heads/'.$currentBranch);

    $this->output(sprintf('Published feature %s', $currentBranch), Output::SCOPE_PUBLIC);

    return 0;
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "pushes the feature branch on the origin",
    );
  }
}