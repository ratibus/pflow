<?php

class Pflow_Command_Autocomplete extends Pflow_Command
{
  /**
   * @param string $argv
   * @return int
   */
  public function execute($argv)
  {
    $rawCommandToAutocomplete = isset($argv[2]) ? $argv[2] : '';
    
    // If the command ends with a space, it means the previous completion was successful
    // Otherwise the command has a completion in progress
    $lastChar = substr($rawCommandToAutocomplete, -1);
    
    $partialCommand        = $lastChar !== ' ';
    $optionsRequested      = $lastChar === '-';
    $commandToAutocomplete = trim($rawCommandToAutocomplete);
    
    $commandToAutocompleteParts = explode(' ', $commandToAutocomplete);
    
    // We remove first part of the command (pflow or pflowdev for example)
    array_shift($commandToAutocompleteParts);
    
    if ($partialCommand)
    {
      // We remove partial command part at the end of the command
      array_pop($commandToAutocompleteParts);
    }
    
    $depth = count($commandToAutocompleteParts);
    
    $keywords = array();
    $autocompleteOptions = array();
    foreach ($this->getCommandClasses() as $commandClass)
    {
      $command = strtolower(str_replace(array('Pflow_Command_'), array(''), $commandClass));
      $commandWords = explode('_', $command);
      
      if ($commandWords[0] === 'autocomplete')
      {
        continue;
      }

      // This loop validates that $commandClass matches fully $commandToAutocomplete for the required $depth
      $validCommand = true;
      for($i = 0; $i < $depth; $i++)
      {
        if (!isset($commandWords[$i]) || $commandWords[$i] !== $commandToAutocompleteParts[$i])
        {
          $validCommand = false;
          break;
        }
      }
      
      // If $commandClass matches and has subcommands
      if ($validCommand && isset($commandWords[$depth]))
      {
        $keywords[$commandWords[$depth]] = $commandWords[$depth];
      }
      
      // Fully qualified command found
      if ($optionsRequested && $validCommand && count($commandWords) == $depth)
      {
        $help = call_user_func(array($commandClass, 'getHelp'));
        if (isset($help['options']))
        {
          $autocompleteOptions = array_keys($help['options']);
        }
      }
    }
    
    ksort($keywords);
    
    // We add option to autocomplete
    foreach ($autocompleteOptions as $autocompleteOption)
    {
      list($autocompleteOptionKeyword) = explode(':', $autocompleteOption);
      $autocompleteOptionKeyword = '-'.$autocompleteOptionKeyword;
      $keywords[$autocompleteOptionKeyword] = $autocompleteOptionKeyword;
    }
    
    echo implode(" ", $keywords);
    
    return 1;
  }
  
  /**
   * Return available command names 
   * @return array
   */
  protected function getCommandClasses()
  {
    $autoloader = new Pflow_Autoloader(PFLOW_ROOT_DIR);
    $helpBuilder = new Pflow_HelpBuilder();
    $helpBuilder->setLibDirs($autoloader->getClassDirs());
    return $helpBuilder->getCommandClasses();
  }
  
  /**
   * @static
   * @return array
   */
  public static function getHelp()
  {
    return array(
      'description' => "returns autocomplete keywords",
    );
  }
}