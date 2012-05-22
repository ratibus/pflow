<?php

require_once dirname(__FILE__).'/../lib/pmsipilot-flow/pflow_getopt.php';

$tests = array(
  array('db:np', 'feature start 42 -b 7.4 -p', array('b' => '7.4', 'p' => true)),
  array('db:np', 'feature start 42 - 7.4 -p', array('p' => true)),
  array('h:v:', 'hudson show -h http://localhost:8080/', array('h' => 'http://localhost:8080/')),
);

$fails = 0;

foreach($tests as $i => $test)
{
  list($def, $argv, $expect) = $test;
  
  $argv = explode(' ', $argv);
  $got  = pflow_getopt($argv, $def);

  if ($got !== $expect)
  {
    $fails++;
    echo sprintf('>> failed test #%d (%s) (got: %s, expected: %s', $i, $def, var_export($got, true), var_export($expect, true)).PHP_EOL;
  }
}

if ($fails > 0)
{
  echo 'Failed '.$fails.' tests';
}
else
{
  echo 'All clear!';
}

echo PHP_EOL;