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

namespace Drupal\apigee_m10n\TwigExtension;

/**
 * Provides a Twig extension to format currency.
 */
class LocaleGetDisplayNameTwigExtension extends \Twig_Extension {

  /**
   * @inheritdoc
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('apigee_m10n_locale_get_display_name', [$this, 'localeGetDisplayName']),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getName() {
    return 'apigee_m10n.locale_get_display_name';
  }

  /**
   * Takes a 2 character iso 3166 country code and returns the associated display name.
   *
   * @param $iso_country_code
   *
   * @return string
   */
  public static function localeGetDisplayName($iso_country_code) {
    return \Locale::getDisplayName("-$iso_country_code");
  }

}
