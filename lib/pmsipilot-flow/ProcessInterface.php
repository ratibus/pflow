<?php

interface ProcessInterface
{
  public function execute();

  public function getInputStream();

  public function getOutputStream();

  public function getErrorStream();

  public function getExitCode();

  public function isSuccess();
}