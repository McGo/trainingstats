# Trainingstats
PHP package to gather metrics and calculate statistics from a training TCX file.

## Installation

Simply install the package via composer or drop it in manually and add the autoloader.

## Usage

There are two classes that are important to be aware of. The parsing is done by TCXParser() which leads to an object that stores activities and the accoring data. You could use the second Class Calculation() on the parsers activity then to check details.

### Parse a tcx file
$parser = new TCXParser();
$parser->parse('/path/to/tcx/file.tcx');

### Calculate details from the parser object
$activities = $parser->getActivities();
foreach ($activities as $activity) {
  $calculation = new Calculation($activity);
  try {
  $details = $calculation->calculateBasicDetails();
    $splits = $calculation->calculateSplits();
    $laps = $calculation->calculateLapDetails();
    $distances = $calculation->calculateBestDistances();
    $times = $calculation->calculateBestTimes();

  }
  catch (ActivityIsNotSetException $e) {
    // Do what you want to do if the file is tcx but has no activities
  }
  catch (FileIsNoTCXFileException $e) {
    // Handle the situation when the file is not a tcx file
  }
}

## To Do

* Documentation
* Add Heartratezone calculation
* Add kcal calculation

[![Build Status](https://travis-ci.org/McGo/trainingstats.svg)](https://travis-ci.org/McGo/trainingstats)
