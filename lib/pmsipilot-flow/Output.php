<?php

/**
 *
 */
abstract class Output implements OutputInterface
{
  const SCOPE_PRIVATE = 'private';
  const SCOPE_PUBLIC  = 'public';
  const SCOPE_DEBUG   = 'debug';
  const SCOPE_STANDARD_STREAMS = 'standard_streams';
}