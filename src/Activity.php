<?php namespace Trainingstats;

class Activity {
  /**
   * The time when the activity started
   *
   * @var \DateTime
   */
  protected $startTime;

  /**
   * Points
   *
   * @var array
   */
  protected $points = array();

  /**
   * Lap summaries
   *
   * @var array
   */
  protected $laps = array();

  protected $totalDistance = 0;
  protected $movementDuration = 0;
  protected $totalDuration = 0;
  protected $maxHeartrate = 0;
  protected $avgHeartrate = 0;
  protected $avgPace = 0;
  protected $maxPace = 0;
  protected $kcal = 0;
  protected $maxElevation = 0;
  protected $minElevation = 0;

  /**
   * The constructor to create a new activity based on the xml node.
   * @param $activityNode
   */
  public function __construct($activityNode) {

    date_default_timezone_set('Europe/Berlin');
    $this->startTime = new \DateTime((string) $activityNode->Id);

    foreach ($activityNode->Lap as $lapNode) {
      $laps[] = $this->parseLap($lapNode);
    }

    if (count($laps) > 0) {
      // Only set the laps if there is at least one
      $this->setLaps($laps);

      // Calculate metrics
      $this->calculateMetrics();
    }
    return $this;
  }

  /**
   * Convert speed value from m/s to km/h
   *
   * @param  float $speed The speed in m/s
   * @return float The speed in km/h
   */
  protected function convertSpeed($speed) {
    return $speed * 3.6;
  }

  protected function parseLap(\SimpleXMLElement $lapNode) {
    $startIndex = count($this->getPoints());
    $this->parseTrack($lapNode->Track);
    return new Lap($startIndex, count($this->getPoints()) - 1);
  }

  protected function parseTrack(\SimpleXMLElement $trackNode) {
    foreach ($trackNode->Trackpoint as $trackpointNode) {
      $point = $this->parseTrackpoint($trackpointNode);
      if ($point) {
        $this->addPoint($point);
      }
    }
  }

  protected function parseTrackpoint(\SimpleXMLElement $trackpointNode) {
    // Skip the point if lat/lng not found
    if (!isset($trackpointNode->Position->LatitudeDegrees) || !isset($trackpointNode->Position->LongitudeDegrees)) {
      return;
    }
    $point = new Point();
    $point->setElevation((float) $trackpointNode->AltitudeMeters);
    $point->setDistance((float) $trackpointNode->DistanceMeters);
    $point->setLatitude((float) $trackpointNode->Position->LatitudeDegrees);
    $point->setLongitude((float) $trackpointNode->Position->LongitudeDegrees);
    $point->getTime()->modify((string) $trackpointNode->Time);
    if (isset($trackpointNode->HeartRateBpm->Value)) {
      $point->setHeartRate((int) $trackpointNode->HeartRateBpm->Value);
    }
    if (isset($trackpointNode->Extensions->TPX->Speed)) {
      $point->setSpeed($this->convertSpeed((float) $trackpointNode->Extensions->TPX->Speed));
    } else {
      // TODO If no speed is set, we can calculate it from the point before.
    }
    return $point;
  }

  public function calculateMetrics() {
    $points = $this->getPoints();
    $count = count($points);
    $keys = array_keys($points);
    $start = $points[$keys[0]];
    $end = $points[$keys[count($keys) - 1]];

    if ($count === 1) {
      $this->totalDistance = $start->getDistance();
    }
    else {
      // Total Distance
      $this->totalDistance = $end->getDistance() - $start->getDistance();

      // Maximum Elevation
      $this->maxElevation = (int) max(array_map(function ($point) {
        return $point->getElevation();
      }, $points));

      // Minimum Heartrate
      $this->minElevation = (int) min(array_map(function ($point) {
        return $point->getElevation();
      }, $points));

      // Maximum Heartrate
      $this->maxHeartrate = (int) max(array_map(function ($point) {
        return $point->getHeartRate();
      }, $points));

      // Average Heartrate
      $values = array_map(function ($point) {
        return $point->getHeartRate();
      }, $points);
      $this->avgHeartrate = array_sum($values) / $count;

      // Duration
      $this->totalDuration = $end->getTimestamp() - $start->getTimestamp();

      // Moving duration
      $movingtime = $this->getTotalDuration();
      for ($index = $keys[0] + 1; $index < $count; $index++) {
        $interval = $points[$index]->getTimestamp() - $points[$index - 1]->getTimestamp();
        if ($interval > 10 || $points[$index]->getSpeed() <= 1) {
          $movingtime -= $interval;
        }
      }
      // This does not work currently ?! So movement is total
      $this->movementDuration = $movingtime;
      $this->movementDuration = $this->getTotalDuration();

      // Average pace
      $this->avgPace = (($this->getTotalDuration()/60) / ($this->getTotalDistance()/1000));

      // Max Speed
      $this->maxPace = (int) max(array_map(function ($point) {
        return $point->getSpeed();
      }, $points));

      // kcal as the sum of the laps kcal
      $kcal = 0;
      foreach ($this->laps as $lap) {
        // TODO kcal aus lap
      }
    }

  }

  public function getMeasurementSummary() {
    // schnellste strecken ()
    // zeit in heartratezone ()
    // aufstieg
    // abstieg
    // min höhe
    // max höhe
    // wetter

    return array(
      'distance' => $this->getTotalDistance(),
      'duration' => array(
        'total' => $this->getTotalDuration(),
        'movement' => $this->getMovementDuration(),
      ),
      'heartrate' => array(
        'avg' => $this->getAvgHeartrate(),
        'max' => $this->getMaxHeartrate(),
      ),
      'pace' => array(
        'avg' => $this->getAvgPace(),
        'max' => $this->getMaxPace(),
      ),
      'elevation' => array(
        'max' => $this->getMaxElevation(),
        'min' => $this->getMinElevation(),
      ),
      'kcal' => $this->getKcal(),
    );
  }

  public function addPoint(Point $point) {
    $this->points[] = $point;
  }

  public function setPoints(array $points) {
    $this->points = array();
    foreach ($points as $point) {
      $this->addPoint($point);
    }
  }

  public function getPoints() {
    return $this->points;
  }

  public function addLap(Lap $lap) {
    $this->laps[] = $lap;
  }

  public function setLaps(array $laps) {
    $this->laps = array();
    foreach ($laps as $lap) {
      $this->addLap($lap);
    }
  }

  public function getLap($index) {
    return $this->laps[$index];
  }

  public function getLaps() {
    return $this->laps;
  }

  public function getTotalDistance() {
    return $this->totalDistance;
  }

  public function getTotalDuration() {
    return $this->totalDuration;
  }

  public function getMovementDuration() {
    return $this->movementDuration;
  }

  public function getAvgHeartrate() {
    return $this->avgHeartrate;
  }

  public function getMaxHeartrate() {
    return $this->maxHeartrate;
  }

  public function getAvgPace() {
    return $this->avgPace;
  }

  public function getMaxPace() {
    return $this->maxPace;
  }

  public function getKcal() {
    return $this->kcal;
  }

  public function getMaxElevation() {
    return $this->maxElevation;
  }

  public function getMinElevation() {
    return $this->minElevation;
  }

}