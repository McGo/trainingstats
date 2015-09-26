<?php

namespace Trainingstats;

class Lap {
  /**
   * The start index of the Activity points array
   *
   * @var int
   */
  protected $startindex;
  /**
   * The end index of the Activity points array
   *
   * @var int
   */
  protected $endindex;

  protected $totaltime;
  protected $distance;
  protected $maxspeed;
  protected $calories;
  protected $heartrateavg;
  protected $heartratemax;
  protected $intensity;


  public function __construct($lapNode, $startindex, $endindex) {
    $this->startindex = $startindex;
    $this->endindex = $endindex;

    if (isset($lapNode->DistanceMeters)) $this->distance = (float) $lapNode->DistanceMeters;
    if (isset($lapNode->MaximumSpeed)) $this->maxspeed = (float) $lapNode->MaximumSpeed;

    if (isset($lapNode->Calories)) $this->calories = (float) $lapNode->Calories;
    if (isset($lapNode->AverageHeartRateBpm->Value)) $this->heartrateavg= (float) $lapNode->AverageHeartRateBpm->Value;
    if (isset($lapNode->MaximumHeartRateBpm->Value)) $this->heartratemax= (float) $lapNode->MaximumHeartRateBpm->Value;
    if (isset($lapNode->Intensity)) $this->intensity = (string) $lapNode->Intensity;
    if (isset($lapNode->TotalTimeSeconds)) $this->totaltime= (float) $lapNode->TotalTimeSeconds;
  }

  /**
   * @return int
   */
  public function getStart() {
    return $this->startindex;
  }
  /**
   * @return int
   */
  public function getEnd() {
    return $this->endindex;
  }

  /**
   * @return float
   */
  public function getTotaltime() {
    return $this->totaltime;
  }

  /**
   * @return float
   */
  public function getDistance() {
    return $this->distance;
  }

  /**
   * @return float
   */
  public function getMaxspeed() {
    return $this->maxspeed;
  }

  /**
   * @return string
   */
  public function getIntensity() {
    return $this->intensity;
  }

  /**
   * @return float
   */
  public function getHeartratemax() {
    return $this->heartratemax;
  }

  /**
   * @return float
   */
  public function getHeartrateavg() {
    return $this->heartrateavg;
  }

  /**
   * @return float
   */
  public function getCalories() {
    return $this->calories;
  }

  /**
   * @return float
   */
  public function getMaxpace() {
    if ($this->getMaxspeed() > 0) {
      return 60 / $this->maxspeed;
    } else {
      return 0;
    }
  }

  public function getStatistics() {
    return array(
      'startindex' => $this->getStart(),
      'endindex' => $this->getEnd(),
      'totaltime' => $this->getTotaltime(),
      'distance' => $this->getDistance(),
      'maxspeed' => $this->getMaxpace(),
      'maxpace' => $this->getMaxpace(),
      'heartrate' => array(
        'avg' => $this->getHeartrateavg(),
        'max' => $this->getHeartratemax(),
      ),
      'intensity' => $this->getIntensity(),
      'calories' => $this->getCalories(),
    );
  }
}