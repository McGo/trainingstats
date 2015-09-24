<?php

use Trainingstats\Parser\TCXParser;

class TCXParserTest extends PHPUnit_Framework_TestCase {

  public function testTCXFileIsEmpty() {
    $parser = new TCXParser();
    try {
      $parser->parse(NULL);
    } catch (Exception $e) {
      $this->assertContains('Unable to read file', $e->getMessage());
      return;
    }

    $this->fail('Expected Exception is not thrown.');
  }

  public function testTCXFileIsNoTCXFile() {
    $parser = new TCXParser();
    try {
      $parser->parse(__DIR__."/Fixtures/notcx.tcx");
    } catch (\Trainingstats\Exceptions\FileIsNoTCXFileException $e) {
      return;
    }

    $this->fail('Expected Exception is not thrown.');
  }
}