<?php namespace Trainingstats\Parser;

abstract class Parser {
  protected $activities;
  abstract public function parse($file);
}