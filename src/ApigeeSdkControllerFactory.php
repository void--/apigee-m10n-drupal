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

namespace Drupal\apigee_m10n;

use Apigee\Edge\Api\Management\Controller\OrganizationController;
use Apigee\Edge\Api\Management\Controller\OrganizationControllerInterface;
use Apigee\Edge\Api\Monetization\Controller\ApiPackageController;
use Apigee\Edge\Api\Monetization\Controller\ApiPackageControllerInterface;
use Apigee\Edge\Api\Monetization\Controller\CompanyPrepaidBalanceController;
use Apigee\Edge\Api\Monetization\Controller\CompanyPrepaidBalanceControllerInterface;
use Apigee\Edge\Api\Monetization\Controller\DeveloperPrepaidBalanceController;
use Apigee\Edge\Api\Monetization\Controller\DeveloperPrepaidBalanceControllerInterface;
use Apigee\Edge\Api\Monetization\Controller\RatePlanController;
use Apigee\Edge\Api\Monetization\Controller\RatePlanControllerInterface;
use Apigee\Edge\Api\Monetization\Entity\CompanyInterface;
use Drupal\apigee_edge\SDKConnectorInterface;
use Drupal\user\UserInterface;

/**
 * The `apigee_m10n.sdk_controller_factory` service class.
 *
 * @package Drupal\apigee_m10n
 */
class ApigeeSdkControllerFactory implements ApigeeSdkControllerFactoryInterface {

  /**
   * @var \Drupal\apigee_edge\SDKConnectorInterface
   */
  protected $sdk_connector;

  /**
   * The org fo this installation.
   *
   * @var string
   */
  protected $org;

  /**
   * The HTTP Client to use for each controller.
   *
   * @var \Apigee\Edge\ClientInterface
   */
  protected $client;

  /**
   * Monetization constructor.
   *
   * @param \Drupal\apigee_edge\SDKConnectorInterface $sdk_connector
   */
  public function __construct(SDKConnectorInterface $sdk_connector) {
    $this->sdk_connector  = $sdk_connector;
    $this->org            = $sdk_connector->getOrganization();
    $this->client         = $sdk_connector->getClient();
  }

  /**
   * {@inheritdoc}
   */
  public function organizationController(): OrganizationControllerInterface {
    return
      new OrganizationController(
        $this->client
      );
  }

  /**
   * {@inheritdoc}
   */
  public function developerBalanceController(UserInterface $developer): DeveloperPrepaidBalanceControllerInterface {
    return
      new DeveloperPrepaidBalanceController(
        $developer->getEmail(),
        $this->org,
        $this->client
      );
  }

  /**
   * {@inheritdoc}
   */
  public function companyBalanceController(CompanyInterface $company): CompanyPrepaidBalanceControllerInterface {
    return
      new CompanyPrepaidBalanceController(
        $company->getLegalName(),
        $this->org,
        $this->client
      );
  }

  /**
   * {@inheritdoc}
   */
  public function apiPackageController(): ApiPackageControllerInterface {
    return
      new ApiPackageController(
        $this->org,
        $this->client
      );
  }

  /**
   * {@inheritdoc}
   */
  public function packageRatePlanController($package_id): RatePlanControllerInterface {
    return
      new RatePlanController(
        $package_id,
        $this->org,
        $this->client
      );
  }
}
