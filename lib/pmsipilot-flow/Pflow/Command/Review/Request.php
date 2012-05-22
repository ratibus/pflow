<?php

class Pflow_Command_Review_Request extends Pflow_Command
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
      
    try
    {
      $baseBranch = $this->getConfig(sprintf('branch.%s.base', $branch));
    }
    catch (InvalidArgumentException $e)
    {
      $this->output(sprintf('Unable to find base branch configuration for branch %s.', $branch));
  
      return 1;
    }
    
    $lastCommit = $this->git->getLastCommit($branch);
    
    $crew    = new Crew($crewUrl);
    $message = $crew->reviewRequest($crewProjectId, $branch, $baseBranch, $lastCommit);
    
    $this->output($message);
    
    return 0;
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "create a review request on Crew server for current branch",
    );
  }
}