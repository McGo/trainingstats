<?php namespace Trainingstats;

use Trainingstats\Exceptions\ActivityIsNotSetException;

class Calculation {

  // Distances Variable
  private $distances = array(
    '1km' => array('distance' => 1000),
    '3km' => array('distance' => 3000),
    '5km' => array('distance' => 5000),
    '10km' => array('distance' => 10000),
    'hm' => array('distance' => 21000),
    'marathon' => array('distance' => 42195),
    '1mile' => array('distance' => 1609),
    '3mile' => array('distance' => 4827),
  );

  // Times Variable
  private $times = array(
    '12min' => array('time' => 720),
    '60min' => array('time' => 3600),
  );

  // The blur that is used when calculating distance and time details.
  private $blur = 15;


  // Split variable
  private $split = 1000;

  // The Activity to run calculation an.
  private $activity = NULL;

  /**
   * Constructor with the activity to calculate
   *
   * @param $activity
   */
  public function __construct($activity) {
    $this->activity = $activity;
  }

  /**
   * Convert given mile to km.
   *
   * @param $mile
   * @return float
   */
  public function mTokm($mile) {
    return (float) $mile / 1.609344;
  }

  /**
   * Convert given km to mile
   * @param $km
   * @return float
   */
  public function kmTom($km) {
    return (float) $km * 1.609344;
  }

  /**
   * Based on the given points, this function calculates the splits (default to
   * 1km)
   *
   * @return array
   * @throws \Trainingstats\Exceptions\ActivityIsNotSetException
   */
  public function calculateSplits() {
    if ($this->activity === NULL) {
      throw new ActivityIsNotSetException();
    }
    $points = $this->activity->getPoints();
    $distance = $this->split;
    $splits = array();
    $index = 1;
    foreach ($points as $pointid => $point) {
      if ($point->getDistance() > ($distance * $index)) {

        // Calculate the pace
        $lastpoint = $index === 1 ? $points[0] : $points[$splits[($index - 1)]['pointid']];

        $splits[$index] = array(
          'distance' => $index * $distance,
          'time' => $point->getTime(),
          'split' => $index,
          'pace' => $this->calculatePace($point, $lastpoint),
          'pointid' => $pointid,
        );
        $index++;
      }
    }
    return $splits;
  }

  /**
   * Calculates the best paces for distances. The distances could be set manually
   * but default to these values:
   *  '1km' => array('distance' => 1000),
   *  '3km' => array('distance' => 3000),
   *  '5km' => array('distance' => 5000),
   *  '10km' => array('distance' => 10000),
   *  'hm' => array('distance' => 21000),
   *  'marathon' => array('distance' => 42195),
   *  '1mile' => array('distance' => 1609),
   *  '3mile' => array('distance' => 4827),
   *
   * @return array
   * @throws \Trainingstats\Exceptions\ActivityIsNotSetException
   */
  public function calculateBestDistances() {
    if ($this->activity === NULL) {
      throw new ActivityIsNotSetException();
    }
    $distances = $this->getDistances();
    $points = $this->activity->getPoints();
    $best = $distances;
    $calculatedDistances = array();

    foreach ($points as $pointid => $point) {
      if ($pointid % $this->blur == 0) {
        // Reset the calculated Distances array.
        $calculatedDistances[$pointid] = array();

        // Based on this pointid, look through all points that follow and
        // check if the distance threshhold was reached.
        for ($x = $pointid; $x < count($points); $x++) {
          if ($x % $this->blur == 0) {
            $newpoint = $points[$x];
            foreach ($distances as $key => $distance) {
              if (!isset($calculatedDistances[$pointid][$key]) && $newpoint->getDistance() - $point->getDistance() > $distance['distance']) {
                $calculatedDistances[$pointid][$key] = array(
                  'pace' => $this->calculatePace($point, $newpoint),
                  'startpoint' => $pointid,
                  'endpoint' => $x,
                );
              }
            }
          }
        }
      }
    }

    foreach ($calculatedDistances as $calculatedDistance) {
      foreach ($calculatedDistance as $key => $values) {
        if (!isset($best[$key]['pace'])) {
         $best[$key] += $values;
        } else {
          if ($best[$key]['pace'] > $values['pace']) {
            $best[$key]['pace'] = $values['pace'];
            $best[$key]['startpoint'] = $values['startpoint'];
            $best[$key]['endpoint'] = $values['endpoint'];
          }
        }
      }
    }

    return $best;
  }

  public function calculateBestTimes() {
  }

  /**
   * Returns all defined distances to calculate pace for
   * @return array
   */
  public function getDistances() {
    return $this->distances;
  }

  /**
   * Set distance values for calculation. This can be used to alter the default
   * values for calculation, which is:
   *  '1km' => array('distance' => 1000),
   *  '3km' => array('distance' => 3000),
   *  '5km' => array('distance' => 5000),
   *  '10km' => array('distance' => 10000),
   *  'hm' => array('distance' => 21000),
   *  'marathon' => array('distance' => 42195),
   *  '1mile' => array('distance' => 1609),
   *  '3mile' => array('distance' => 4827),
   *
   * @param $value An array containing the distancesin the format
   *   array(
   *    'key' => array('distance' => 720),
   *   );
   */
  public function setDistances($value) {
    // TODO Check for array values.
    $this->distances = $value;
  }

  /**
   * Returns all defined times to calculate pace for.
   *
   * @return array with keys that has to be calculated. Each entry is an array
   *   with a key 'time' that holds the times to be caclulcated in seconds.
   */
  public function getTimes() {
    return $this->times;
  }

  /**
   * Set time values for time calculation. This can be used to alter the default
   * values for calculation, which is 12 min (Cooper) and 1 hour
   *
   * @param $value An array containing the times in the format
   *   array(
   *    '12min' => array('time' => 720),
   *    '60min' => array('time' => 3600),
   *    'aday' => array('time' => 86400),
   *   );
   */
  public function setTimes($value) {
    // TODO Check for array values
    $this->times = $value;
  }

  /**
   * Get the current set split in meters that will be used for split calculation
   *
   * @return int
   */
  public function getSplit() {
    return $this->split;
  }

  /**
   * Set the value that will be used to calculation split based on distance.
   *
   * @param $value An integer value that represents the split in meters
   */
  public function setSplit($value) {
    $this->split = $value;
  }

  /**
   * Gets the value that will be used as blur to calculate best distances and
   * best times.
   *
   * @return int
   */
  public function getBlur() {
    return $this->blur;
  }

  /**
   * Set the blur value to increase or decrease accurancy in best time and
   * distance calculation. The value means how many points in the tcx file
   * will be skipped, so when setting this value to 10, each 10th point will
   * be used. Setting the value to 1 means that a calculation will be done for
   * each point which increases accurancy but directly impacts the calculation
   * time. A value of 15 is default and a good choise. Tests showed a difference
   * of < 1 second in pace by decreasing calculation time a lot.
   *
   * @param int $blur
   */
  public function setBlur($blur) {
    $this->blur = $blur;
  }

  /**
   * Calculates the pace in seconds between two points.
   *
   * @param $point the more current Point()
   * @param $lastpoint the later Point()
   * @return float the pace as float representing minutes per km.
   */
  private function calculatePace($point, $lastpoint) {
    // Get the distance in meter and recalc in km
    $distance = $point->getDistance() - $lastpoint->getDistance();
    $distance = $distance / 1000;

    // Get the time difference in seconds and recalc in minutes
    $time = $point->getTimestamp() - $lastpoint->getTimestamp();
    $time = $time / 60;

    // The pace is minutes per kilometer
    return $time / $distance;
  }


  public function calculateBasicDetails() {
    if ($this->activity === NULL) {
      throw new ActivityIsNotSetException();
    }

    $points = $this->activity->getPoints();
    $count = count($points);
    $keys = array_keys($points);
    $start = $points[$keys[0]];
    $end = $points[$keys[count($keys) - 1]];

    $result = array();

    if ($count === 1) {
      $result['totalDistance'] = $start->getDistance();
    }
    else {
      // Total Distance
      $result['totalDistance'] = $end->getDistance() - $start->getDistance();

      // Maximum Elevation
      $result['maxElevation'] = (int) max(array_map(function ($point) {
        return $point->getElevation();
      }, $points));

      // Minimum Heartrate
      $result['minElevation'] = (int) min(array_map(function ($point) {
        return $point->getElevation();
      }, $points));

      // Maximum Heartrate
      $result['maxHeartrate'] = (int) max(array_map(function ($point) {
        return $point->getHeartRate();
      }, $points));

      // Average Heartrate
      $values = array_map(function ($point) {
        return $point->getHeartRate();
      }, $points);
      $result['avgHeartrate'] = array_sum($values) / $count;

      // Duration
      $result['totalDuration'] = $end->getTimestamp() - $start->getTimestamp();

      // Moving duration
      $movingtime = $result['totalDuration'];
      for ($index = $keys[0] + 1; $index < $count; $index++) {
        $interval = $points[$index]->getTimestamp() - $points[$index - 1]->getTimestamp();
        if ($interval > 10 || $points[$index]->getSpeed() <= 1) {
          $movingtime -= $interval;
        }
      }
      // This does not work currently ?! So movement is total
      $result['movementDuration'] = $movingtime;
      $result['movementDuration'] = $result['totalDuration'];

      // Average pace
      $result['avgPace'] = (($result['totalDuration']/60) / ($result['totalDistance']/1000));

      // Max Speed
      $result['maxPace'] = (int) max(array_map(function ($point) {
        return $point->getSpeed();
      }, $points));
    }

    return $result;
  }
}