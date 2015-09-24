<?php namespace Trainingstats\Parser;

use Trainingstats\Activity;
use Trainingstats\Exceptions\FileIsNoTCXFileException;

class TCXParser extends Parser {

  protected $activities = array();

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

    $xml = @simplexml_load_file($file);
    if (!$xml || !isset($xml->Activities->Activity)) {
      throw new FileIsNoTCXFileException();
    }

    // Parse all activities
    foreach ($xml->Activities->Activity as $activity) {
      $this->activities[] = new Activity($activity);
    }
  }

  public function getActivities() {
    return $this->activities;
  }

  public function getMeasurements() {
    $measurements = array();
    foreach ($this->getActivities() as $activity) {
      $measurements[] = $activity->getMeasurementSummary();
    }
    return $measurements;
  }
}