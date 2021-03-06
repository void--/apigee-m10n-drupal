<?php
/**
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\Tests\apigee_m10n\Kernel;

use Drupal\apigee_edge\Entity\ApiProduct;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Session\AccountInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

/**
 * Tests the testing framework for testing offline.
 *
 * @group apigee_m10n
 * @group apigee_m10n_kernel
 */
class MonetizationServiceKernelTest extends MonetizationKernelTestBase {

  /**
   * @var \Drupal\apigee_m10n\MonetizationInterface
   */
  protected $monetization;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->monetization = $this->container->get('apigee_m10n.monetization');
  }

  /**
   * Tests that monetization service correctly identifies a monetized
   * organization.
   */
  public function testMonetizationEnabled() {

    // Queue a response from the mock server.
    $this->stack->queueFromResponseFile(['get_organization' => ['monetization_enabled' => 'true']]);

    // Execute a client call.
    $is_monetization_enabled = $this->monetization->isMonetizationEnabled();


    self::assertEquals($is_monetization_enabled, TRUE);
  }

  /**
   * Tests that the monetization service correctly identifies an organization without monetization enabled.
   */
  public function testMonetizationDisabled() {

    if ($this->integration_enabled) {
      $this->markTestSkipped('This test suite is expecting a monetization enabled org. Disable for integration testing.');
    }

    // Queue a response from the mock server.
    $this->stack->queueFromResponseFile('get_organization');

    // Execute a client call.
    $is_monetization_enabled = $this->monetization->isMonetizationEnabled();


    self::assertEquals($is_monetization_enabled, FALSE);
  }

  /**
   * @covers \Drupal\apigee_m10n\Monetization::apiProductAssignmentAccess
   *
   * @throws \Exception
   */
  public function testApiProductAssignmentAccess() {
    $test_product_name = $this->randomMachineName();
    $email = $this->randomMachineName() . '@example.com';

    $entity = ApiProduct::create(['name' => $test_product_name]);

    $account = $this
      ->getMockBuilder(AccountInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $account->expects($this->any())
      ->method('getEmail')
      ->will($this->returnValue($email));

    $this->stack->queueFromResponseFile(['get_eligible_products' => [
      'id' => strtolower($test_product_name),
      'name' => $test_product_name,
    ]]);

    // Test that the user can access the API Product.
    $access_result = $this->monetization->apiProductAssignmentAccess($entity, $account);

    self::assertTrue($access_result instanceof AccessResultAllowed);

    // Tests when a product isn't in the eligible list.
    $test_product_name_2 = $this->randomMachineName();
    $entity = ApiProduct::create(['name' => $test_product_name_2]);

    // Test that the user can access the API Product.
    $access_result = $this->monetization->apiProductAssignmentAccess($entity, $account);
    self::assertTrue($access_result instanceof AccessResultForbidden);
  }

  /**
   * Test the module logger.
   *
   * @throws \Exception
   */
  public function testLogger() {
    $logger = $this->container->get('logger.channel.apigee_m10n');
    static::assertInstanceOf(LoggerInterface::class, $logger);
  }
}
