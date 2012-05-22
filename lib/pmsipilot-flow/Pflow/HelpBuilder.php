<?php

class Pflow_HelpBuilder
{
  /**
   * Directories to scan
   * @var array
   */
  private $libDirs = array();
  
  /**
   * 
   */
  public function __construct()
  {
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
    $helpLines = array();
    
    $commandClasses = $this->getCommandClasses();

    $maxCommandLength = 0;
    $commandInfos     = array();
    foreach ($commandClasses as $commandClass)
    {
      $help = call_user_func(array($commandClass, 'getHelp'));
      
      $command = strtolower(str_replace(array('Pflow_Command_', '_'), array('', ' '), $commandClass));
      
      if (isset($help['arguments']))
      {
        $command .= ' <'.implode('> <', $help['arguments']).'>';
      }
      
      if (isset($help['optional_arguments']))
      {
        $command .= ' [<'.implode('>] [<', $help['optional_arguments']).'>]';
      }
      
      $maxCommandLength = max($maxCommandLength, strlen($command));
      
      $commandInfos[] = array(
        'command'     => $command,
        'description' => isset($help['description']) ? $help['description'] : '',
        'options'     => isset($help['options']) ? $help['options'] : array(),
      );
    }
    
    $linePrefix = '  ';
    $space      = 1;
    
    foreach ($commandInfos as $commandInfo)
    {
      $commandLine = $linePrefix.$commandInfo['command'];
      if (strlen($commandInfo['description']))
      {
        $commandLine .= str_repeat(' ', $space + $maxCommandLength - strlen($commandInfo['command']));
        $commandLine .= $commandInfo['description'];
      }
      $helpLines[] = $commandLine;
      
      foreach ($commandInfo['options'] as $optionKey => $optionLabel)
      {
        $optionPrefix = str_repeat($linePrefix, 2);
        $optionLine = $optionPrefix.'-'.str_replace(':', ' ', $optionKey);
        $optionLine .= str_repeat(' ', $space + $maxCommandLength - strlen($optionLine) + strlen($linePrefix));
        $optionLine .= $optionLabel;
        
        $helpLines[] = $optionLine;
      }
      
      $helpLines[] = "";
    }
    
    $helpMessage = implode("\n", $helpLines);
    
    return $helpMessage;
  }
  
  /**
   * @return array
   */
  protected function getCommandClasses()
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
        
        // Trigger autoload
        class_exists($className);
        
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
