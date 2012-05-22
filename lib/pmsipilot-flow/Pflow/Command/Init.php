<?php

class Pflow_Command_Init extends Pflow_Command
{
  protected $sections = array('crew', 'base-branch', 'database', 'jabber');
  
  /**
   * @param string $argv
   * @return int
   */
  public function execute($argv)
  {
    $options = pflow_getopt($argv, 'ds:');

    // uninstall
    if (isset($options['d']))
    {
      return $this->executeUninstall();
    }
    
    if (isset($options['s']))
    {
      if (!in_array($options['s'], $this->sections))
      {
        throw new InvalidArgumentException(sprintf("Invalid section option. Only the following values are accepted: %s", implode(',', $this->sections)));
      }
      $sectionsToConfigure = array($options['s']);
    }
    else
    {
      $sectionsToConfigure = $this->sections;
    }

    $this->output('PMSIpilot flow configuration');

    if (in_array('crew', $sectionsToConfigure))
    {
      $this->configureCrew();
    }

    if (in_array('base-branch', $sectionsToConfigure))
    {
      $this->configureBaseBranch();
    }

    if (in_array('database', $sectionsToConfigure))
    {
      $this->configureDatabase();
    }

    if (in_array('jabber', $sectionsToConfigure))
    {
      $this->configureJabber();
    }

    $this->git->setConfig('pflow.installed', 1);

    $this->output('thank you, pflow is now configured!');

    return 1;
  }

  /**
   * @return int
   */
  public function executeUninstall()
  {
    $this->git->removeConfigSection('pflow');
    $this->git->removeConfigSection('pflow.db');
    $this->git->removeConfigSection('pflow.jabber');
    $this->git->removeConfigSection('pflow.crew');
    $this->output('Removed all PMSIpilot flow configuration.');

    return 1;
  }
  
  /**
   * 
   */
  private function configureCrew()
  {
    $this->output('-!- PMSIpilot flow can work with Crew');
    $use_crew = $this->input('handle Crew stuff', 'no', array('yes', 'no')) == 'yes' ? 1 : 0;
    
    if ($use_crew)
    {
      do
      {
        $crew_url = $this->input('Crew URL');
        $crew = new Crew($crew_url);
        $crewProjects = $crew->getProjects();
        $isCrewUrlValid = is_array($crewProjects);
      } while(!$isCrewUrlValid && $this->output("Crew URL is invalid"));
      
      $validCrewProjectIds = array();
      foreach($crewProjects as $crewProject)
      {
        $crewProject = (array)$crewProject;
        $validCrewProjectIds[] = $crewProject['id'];
        $this->output(sprintf('%d: %s (repository: %s)', $crewProject['id'], $crewProject['name'], $crewProject['remote']));
      }
      $this->git->setConfig('pflow.crew.project-id', (integer) $this->input('project selection', null, $validCrewProjectIds));
      $this->git->setConfig('pflow.crew.url', $crew_url);
    }
  }
  
  /**
   * 
   */
  private function configureDatabase()
  {
    $this->output('-!- PMSIpilot flow can automatically create a database when you start a branch (using the -d option of feature start)');
    
    $use_database = $this->input('handle database stuff', 'no', array('yes', 'no')) == 'yes' ? 1 : 0;
    $this->git->setConfig('pflow.db.enabled', $use_database);

    if ($use_database)
    {
      $this->git->setConfig('pflow.db.user',         $this->input('database username', 'root'));
      $this->git->setConfig('pflow.db.use-password', $this->input('use database password', 'no', array('yes', 'no')) == 'yes' ? 1 : 0);
      $this->git->setConfig('pflow.db.template',     $this->input('database name template', 'pmsipilot_%branch%'));
    }
  }
  
  /**
   * 
   */
  private function configureJabber()
  {
    $this->output('PMSIpilot can announce branch starts and merges on jabber. You will need your jabber username and password.');
    $use_jabber = $this->input('announce stuff on jabber', 'no', array('yes', 'no')) == 'yes' ? 1 : 0;
    $this->git->setConfig('pflow.jabber.enabled', $use_jabber);

    if ($use_jabber)
    {
      $this->git->setConfig('pflow.jabber.host', $this->input('your jabber server'));
      $this->git->setConfig('pflow.jabber.port', $this->input('your jabber port', 5222));
      $this->git->setConfig('pflow.jabber.username', $this->input('your jabber user'));
      $this->git->setConfig('pflow.jabber.password', $this->input('your jabber password'));
      $this->git->setConfig('pflow.jabber.nickname', $this->input('your nickname', getenv('LOGNAME')));
      $this->git->setConfig('pflow.jabber.chatroom', $this->input('where to talk'));
      // TODO validate chatroom connection
    }
  }
  
  /**
   * 
   */
  private function configureBaseBranch()
  {
    $this->git->fetch('origin');
    do
    {
      $baseBranch = $this->input('team branch name');
    } while (!$this->git->hasRemoteBranch('origin/'.$baseBranch) && $this->output(sprintf('Branch "%s" does not exist on origin', $baseBranch)));

    $this->git->setConfig('pflow.base', $baseBranch);

    if (!$this->git->hasBranch($baseBranch))
    {
      $this->git->createBranch($baseBranch, 'origin/'.$baseBranch);
    }
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "initialize PMSIpilot flow",
      'options'     => array(
        'd'         => "remove all PMSIpilot flow configuration",
        's:section' => "section to configure",
      ),
    );
  }
}