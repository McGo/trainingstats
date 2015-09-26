<?php

namespace Trainingstats;

class Lap {
  /**
   * The start index of the Activity points array
   *
   * @var int
   */
  protected $start;
  /**
   * The end index of the Activity points array
   *
   * @var int
   */
  protected $end;

  protected $totaltime;
  protected $distance;
  protected $maxspeed;
  protected $maxpace;
  protected $calories;
  protected $heartrateavg;
  protected $heartratemax;
  protected $intensity;


  public function __construct($lapNode, $startindex, $endindex) {
    $this->setStart($startindex);
    $this->setEnd($endindex);

    if (isset($lapNode->DistanceMeters)) $this->distance = (float) $lapNode->DistanceMeters;
    if (isset($lapNode->MaximumSpeed)) {
      $this->maxspeed = (float) $lapNode->MaximumSpeed;
      $this->maxpace = 60 / ((float) $lapNode->MaximumSpeed);
    }
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
    return $this->start;
  }

  /**
   * @param int $start
   */
  public function setStart($start) {
    $this->start = $start;
  }

  /**
   * @return int
   */
  public function getEnd() {
    return $this->end;
  }

  /**
   * @param int $end
   */
  public function setEnd($end) {
    $this->end = $end;
  }
}