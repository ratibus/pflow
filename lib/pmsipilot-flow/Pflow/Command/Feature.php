<?php

abstract class Pflow_Command_Feature extends Pflow_Command
{
  /**
   * @param $argv
   * @return 
   */
  protected function getFeatureName($argv)
  {
    if (!isset($argv[3]))
    {
      throw new InvalidArgumentException("Feature name is mandatory");
    }
    $name = $argv[3];
    if (!strlen($name))
    {
      throw new InvalidArgumentException("Feature name cannot be empty");
    }
    return $name;
  }
}
