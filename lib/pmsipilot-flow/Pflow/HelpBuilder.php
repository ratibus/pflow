<?php

class Pflow_HelpBuilder
{
  /**
   * Directories to scan
   * @var array
   */
  private $libDirs = array();

  /**
   * Package informations
   *
   * @var array
   */
   private $infos = array();

  /**
   * @param object $infos
   */
    public function __construct($infos)
  {
      $this->infos = (array)$infos;
  }

  /**
   * @param $libDirs
   */
  public function setLibDirs($libDirs)
  {
    $this->libDirs = $libDirs;
  }
  
  /**
   * @return string
   */
  public function getHelp()
  {
    $helpLines = array(
        " \033[0;32m" . $this->infos['name'] . "\033[0m version \033[0;33m" . $this->infos['version'] . "\033[0m (" . $this->infos['homepage'] . ")",
        " \033[0;34m" . $this->infos['description'] . "\033[0m",
        " \033[0;34mMaintainer : " . $this->infos['maintainers'][0]->name . "<" . $this->infos['maintainers'][0]->email . ">\033[0m",
        PHP_EOL,
        " Usage: \033[0;32mgit pflow \033[0;33m<command> [options]\033[0m",
        " \033[0;34mAll configuration settings are stored in your \033[1;34m\".git/config\"\033[0;34m under the \033[1;34m\"pflow\"\033[0;34m namespace.\033[0m",
        PHP_EOL,
        "  \033[0;32mGlobal options :\033[0m",
        "    \033[0;33m--version\033[0m                  displays information about pflow version and installation dir",
        "    \033[0;33m--no-hook\033[0m                  if passed, pre/post command hooks will not be executed",
        "  \033[0;32mupdate\033[0m                       updates pflow (requires writing privileges to install prefix)",
    );
    
    $commandClasses = $this->getCommandClasses();

    $maxCommandLength = 0;
    $commandInfos     = array();
    foreach ($commandClasses as $commandClass)
    {
      $command = strtolower(str_replace(array('Pflow_Command_', '_'), array('', ' '), $commandClass));

      $help = call_user_func(array($commandClass, 'getHelp'));
      $help['description']        = isset($help['description'])        ? $help['description']        : '';
      $help['arguments']          = isset($help['arguments'])          ? $help['arguments']          : array();
      $help['optional_arguments'] = isset($help['optional_arguments']) ? $help['optional_arguments'] : array();
      $help['options']            = isset($help['options'])            ? $help['options']            : array();

      $numArgs    = count($help['arguments']);
      $numOptArgs = count($help['optional_arguments']);

      $maxCommandLength = max(
          $maxCommandLength,
          strlen($command) +
              ($numArgs + ($numArgs * 2) + strlen(implode(' ', $help['arguments']))) +
              ($numOptArgs + ($numOptArgs * 4)  + strlen(implode(' ', $help['optional_arguments'])))
      );

      $commandInfos[] = array(
        'command'            => $command,
        'arguments'          => $help['arguments'],
        'optional_arguments' => $help['optional_arguments'],
        'description'        => $help['description'],
        'options'            => $help['options'],
      );
    }

    foreach ($commandInfos as $commandInfo)
    {
      $commandLine = sprintf(
        '  %-' . ($maxCommandLength + 18) . 's %s',
        sprintf(
            "\033[0;32m%s\033[0;33m%s%s\033[0m",
            $commandInfo['command'],
            empty($commandInfo['arguments'])          ? '' : ' <' . implode('> <', $commandInfo['arguments']) . '>',
            empty($commandInfo['optional_arguments']) ? '' : ' [<' . implode('>] [<', $commandInfo['optional_arguments']) . '>]'
        ),
        $commandInfo['description']
      );
      $helpLines[] = $commandLine;

      foreach ($commandInfo['options'] as $optionKey => $optionLabel)
      {
        $helpLines[] = sprintf(
            "    \033[0;33m-%-" . ($maxCommandLength - 3) . "s\033[0m %s",
            str_replace(':', ' ', $optionKey),
            $optionLabel
        );
      }
    }
    
    $helpMessage = implode(PHP_EOL, $helpLines);
    
    return $helpMessage;
  }
  
  /**
   * @return array
   */
  public function getCommandClasses()
  {
    $classes = array();
    
    foreach ($this->libDirs as $libDir)
    {
      $commandDir = $libDir.'/Pflow/Command/';
      $ite = new RecursiveDirectoryIterator($commandDir);
      foreach (new RecursiveIteratorIterator($ite) as $fileName => $splFile)
      {
        if ($splFile->isDir())
        {
          continue;
        }
        
        $className = str_replace('/', '_', substr($fileName, strlen($libDir.'/'), -4));

        $reflectionClass = new ReflectionClass($className);
        
        if ($reflectionClass->IsInstantiable() && $reflectionClass->implementsInterface('Pflow_CommandInterface'))
        {
          $classes[] = $className;
        }
      }
    }
    sort($classes);
    
    return $classes;
  }
}
