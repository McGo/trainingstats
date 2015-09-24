<?php namespace Trainingstats\Parser;

use Trainingstats\Exceptions\FileIsNoTCXFileException;
use Trainingstats\Activity;

class TCXParser extends Parser {

  /**
   * Parse the given file
   * @param $file
   * @throws \Exception
   * @throws \Trainingstats\Exceptions\FileIsNoTCXFileException
   */
  public function parse($file) {
    if (!is_file($file)) {
      throw new \Exception(sprintf('Unable to read file "%s"', $file));
    }

    // A TCX file could hold more than one activity, especially on multi sport
    // activities.
    $activities = array();

    $xml = @simplexml_load_file($file);
    if (!$xml || !isset($xml->Activities->Activity)) {
      throw new FileIsNoTCXFileException();
    }

    // Parse all activities
    foreach ($xml->Activities->Activity as $activity) {
      $activities[] = new Activity($activity);
    }
  }
}