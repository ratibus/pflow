<?php

/**
 * @param array $argv
 * @param string $def
 * @return array
 */
function pflow_getopt($argv, $def)
{
  $options = array();

  foreach($argv as $i => $arg)
  {
    $option = trim($arg, '-');
    
    if (!strlen($option))
    {
      continue;
    }

    if (false !== $pos = strpos($def, $option))
    {
      if (substr($def, $pos + 1, 1) == ':')
      {
        $options[$option] = $argv[$i + 1];
      }
      else
      {
        $options[$option] = true;
      }
    }
  }

  return $options;
}