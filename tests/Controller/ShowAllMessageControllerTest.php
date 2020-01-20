<?php

namespace Drupal\ms_react\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the ms_react module.
 */
class ShowAllMessageControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "ms_react ShowAllMessageController's controller functionality",
      'description' => 'Test Unit for module ms_react and controller ShowAllMessageController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests ms_react functionality.
   */
  public function testShowAllMessageController() {
    // Check that the basic functions of module ms_react.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
