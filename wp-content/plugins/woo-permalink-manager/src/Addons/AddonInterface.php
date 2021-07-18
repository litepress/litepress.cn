<?php namespace Premmerce\UrlManager\Addons;

interface AddonInterface
{
  /**
   * Check if addon is active
   */
  public function isActive();

  /**
   * Innit addon
   */
  public function init();
}