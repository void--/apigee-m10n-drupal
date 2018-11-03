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

namespace Drupal\apigee_m10n\Controller;

use Apigee\Edge\Api\Monetization\Controller\RatePlanControllerInterface;
use Drupal\apigee_m10n\ApigeeSdkControllerFactoryInterface;
use Drupal\apigee_m10n\Entity\RatePlan;
use Drupal\apigee_m10n\Form\RatePlanConfigForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Generates the packages page.
 *
 * @package Drupal\apigee_m10n\Controller
 */
class PackagesController extends ControllerBase {

  /**
   * Service for instantiating SDK controllers.
   *
   * @var \Drupal\apigee_m10n\ApigeeSdkControllerFactoryInterface
   */
  protected $controller_factory;

  /**
   * PackagesController constructor.
   *
   * @param \Drupal\apigee_m10n\ApigeeSdkControllerFactoryInterface $sdk_controller_factory
   *   The SDK controller factory.
   */
  public function __construct(ApigeeSdkControllerFactoryInterface $sdk_controller_factory) {
    $this->controller_factory = $sdk_controller_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('apigee_m10n.sdk_controller_factory'));
  }

  /**
   * Redirect to the users catalog page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function myCatalog(): RedirectResponse {
    return $this->redirect(
      'apigee_monetization.packages',
      ['user' => \Drupal::currentUser()->id()],
      ['absolute' => TRUE]
    );
  }

  /**
   * Redirect to the users subscriptions page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function mySubscriptions(): RedirectResponse {
    return $this->redirect(
      'apigee_monetization.subscriptions',
      ['user' => \Drupal::currentUser()->id()],
      ['absolute' => TRUE]
    );
  }

  /**
   * Gets a list of available packages for this user.
   *
   * @param \Drupal\user\UserInterface|NULL $user
   *   The drupal user/developer.
   *
   * @return array
   *   The pager render array.
   */
  public function catalogPage(UserInterface $user = NULL) {
    // Get the package controller.
    $package_controller = $this->controller_factory->apiPackageController();

    // Initialize empty packages array in case API call fails.
    $packages = [];

    try {
      // Load purchased packages for comparison.
      $packages = $package_controller->getAvailableApiPackagesByDeveloper($user->getEmail(), TRUE, TRUE);
    }
    catch (\Exception $e) {
      $this->getLogger('apigee_monetization')->error($e->getMessage());
      $this->messenger()->addError('Unable to retrieve packages: ' . $e->getMessage());
    }

    // Get the view mode to use for rate plans.
    $view_mode = $this->config(RatePlanConfigForm::CONFIG_NAME)->get('product_rate_plan_view_mode');
    // Get an entity view builder for rate plans.
    $rate_plan_view_builder = $this->entityTypeManager()->getViewBuilder('rate_plan', $view_mode);

    // Initialize empty plans array in case API call fails.
    $plans = [];

    try {
      // Load plans for each package.
      $plans = array_map(function($package) use ($rate_plan_view_builder) {
        // Load the rate plans.
        $package_rate_plans = RatePlan::loadPackageRatePlans($package->id());
        if (!empty($package_rate_plans)) {
          // Return a render-able list of rate plans.
          return $rate_plan_view_builder->viewMultiple($package_rate_plans);
        }
      }, $packages);
    }
    catch (\Exception $e) {
      $this->getLogger('apigee_monetization')->error($e->getMessage());
      $this->messenger()->addError('Unable to retrieve plans: ' . $e->getMessage());
    }

    return [
      'package_list' => [
        '#theme' => 'package_list',
        '#package_list' => $packages,
        '#plan_list' => $plans,
      ]
    ];
  }

  /**
   * Gets a list of purchased subscriptions for this user.
   *
   * @param \Drupal\user\UserInterface|NULL $user
   *   The drupal user/developer.
   *
   * @return array
   *   The pager render array.
   */
  public function subscriptionsPage(UserInterface $user = NULL) {

    $subscriptions = [];

    $sort = \Drupal::request()->getQueryString();

    // Attempt to load accepted plans (i.e. subscriptions)
    try {
      $subscriptions = RatePlan::loadDeveloperSubscriptions($user->getEmail());
    }
    catch (\Exception $e) {
      $this->getLogger('apigee_monetization')->error($e->getMessage());
      $this->messenger()->addError('Unable to retrieve subscriptions: ' . $e->getMessage());
    }

    $render = [
      'subscription_list' => [
        '#type' => 'table',
        '#header' => [
          'status' => ['data' => $this->t('status'), 'field' => 'status'],
          'package' => ['data' => $this->t('package'), 'field' => 'package', 'sort' => 'desc'],
          'products' => ['data' => $this->t('products'), 'field' => 'products'],
          'plan' => ['data' => $this->t('plan'), 'field' => 'plan'],
          'start_date' => ['data' => $this->t('start_date'), 'field' => 'start_date'],
          'end_date' => ['data' => $this->t('end_date'), 'field' => 'end_date'],
          'plan_end_date' => ['data' => $this->t('plan_end_date'), 'field' => 'plan_end_date'],
          'renewal_date' => ['data' => $this->t('renewal_date'), 'field' => 'renewal_date'],
          'actions' =>['data' => $this->t('actions')]
        ]
      ]
    ];

    foreach ($subscriptions as $key => $subscription) {
      $details = $subscription->getRatePlanDetails();
      $package = $subscription->getPackage();

      $render['subscription_list']['#rows'][$key]['status'] = 'qqwer';
      $render['subscription_list']['#rows'][$key]['package'] = $subscription->getPackage()->getDisplayName();
      $render['subscription_list']['#rows'][$key]['products'] = 'qwer';
      $render['subscription_list']['#rows'][$key]['plan'] = count($subscription->getRatePlanDetails());
      $render['subscription_list']['#rows'][$key]['start_date'] = ($start = $subscription->getStartDate()) ? $start->format('Y-m-d') : '';
      $render['subscription_list']['#rows'][$key]['end_date'] = ($end = $subscription->getEndDate()) ? $end->format('Y-m-d') : '';
      $render['subscription_list']['#rows'][$key]['plan_end_date'] = '?';
      $render['subscription_list']['#rows'][$key]['renewal_date'] = '?';
      $render['subscription_list']['#rows'][$key]['actions'] = 'qwer';
    }

    $render['subscription_list']['#empty'] = $this->t('No subscriptions found for this developer.');

    return $render;

  }

  /**
   * Get a rate plan controller.
   *
   * @param $package_id
   *
   * @return \Apigee\Edge\Api\Monetization\Controller\RatePlanControllerInterface
   *   The rate plan controller.
   */
  protected function packageRatePlanController($package_id): RatePlanControllerInterface {
    // Use static caching.
    static $controllers;

    // Controlelrs should be cached per package id.
    if (!isset($controllers[$package_id])) {
      $controllers[$package_id] = $this->controller_factory->packageRatePlanController($package_id);
    }

    return $controllers[$package_id];
  }
}
