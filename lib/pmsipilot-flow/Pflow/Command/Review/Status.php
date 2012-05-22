<?php

class Pflow_Command_Review_Status extends Pflow_Command
{
  /**
   * @param $argv
   * @return int
   * @throws InvalidArgumentException
   */
  public function execute($argv)
  {
    try
    {
      $crewUrl = $this->git->getConfig('pflow.crew.url');
      $crewProjectId = $this->git->getConfig('pflow.crew.project-id');
    }
    catch (InvalidArgumentException $e)
    {
      throw new InvalidArgumentException("Crew is not configured. Use pflow init.");
    }
    
    $branch = $this->git->getCurrentBranch();
    
    $crew    = new Crew($crewUrl);
    $infos   = $crew->reviewStatus($crewProjectId, $branch);

    foreach($infos as $field => $value)
    {
      $this->output(sprintf('%s : %s', $field, $value));
    }
    
    return 0;
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "print review status of the current branch",
    );
  }
}
