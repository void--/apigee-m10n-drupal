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

namespace Drupal\apigee_m10n\EventSubscriber;

use Drupal\apigee_m10n\Monetization;
use Drupal\apigee_m10n\MonetizationInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Validates that monetization is enabled on every request.
 */
class ValidateMonetizationEnabledSubscriber implements EventSubscriberInterface {

  private $monetization;
  private $messenger;

  public function __construct(MonetizationInterface $monetization, MessengerInterface $messenger) {
    $this->messenger = $messenger;
    $this->monetization = $monetization;
  }

  /**
   * If monetization isn't enabled alert the user.
   */
  public function validateMonetizationEnabled() {
    if (!$this->monetization->isMonetizationEnabled()) {
      $this->messenger->addError(Monetization::MONETIZATION_DISABLED_ERROR_MESSAGE);
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('validateMonetizationEnabled');
    return $events;
  }
}