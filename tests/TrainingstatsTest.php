<?php

use McGo\Trainingstats\Trainingstats;

class TrainingstatsTest extends PHPUnit_Framework_TestCase {

  public function testTrainingstatsIsPresent()
  {
    $stats = new Trainingstats();
    $this->assertTrue($stats->isPresent());
  }

}