<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_media\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\Core\Site\Settings;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Behat context for AV Portal.
 */
class AvPortalContext extends RawDrupalContext {

  /**
   * Enables the Mock.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scope.
   *
   * @beforeScenario @av_portal
   */
  public function enableTestModule(BeforeScenarioScope $scope): void {
    $this->enableTestModuleScanning();
    \Drupal::service('module_installer')->install(['media_avportal_mock']);
  }

  /**
   * Disables the Mock.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scope.
   *
   * @afterScenario @av_portal
   */
  public function disableTestModule(AfterScenarioScope $scope): void {
    $this->enableTestModuleScanning();
    \Drupal::service('module_installer')->uninstall(['media_avportal_mock']);
  }

  /**
   * Fills in the Demo content AV Portal reference field.
   *
   * @param string $title
   *   The media title.
   *
   * @Given I reference the AV Portal media :title
   */
  public function assertReferenceAvPortalMedia(string $title): void {
    $this->getSession()->getPage()->fillField('field_oe_demo_av_portal_video[0][target_id]', $title);
  }

  /**
   * Checks that the AV Portal video is rendered.
   *
   * @param string $title
   *   The video title.
   *
   * @Then I should see the AV Portal video :title
   */
  public function assertAvPortalVideoIframe(string $title): void {
    $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $title]);
    if (!$media) {
      throw new \Exception(sprintf('The media named "%s" does not exist', $title));
    }

    $media = reset($media);
    $ref = $media->get('oe_media_avportal_video')->value;
    $this->assertSession()->elementAttributeContains('css', 'iframe', 'src', $ref);
  }

  /**
   * Step that deletes an AV Portal media entity.
   *
   * @param string $title
   *   The media title.
   *
   * @Then I remove the AV Portal media :title
   */
  public function removeAvPortalMedia(string $title): void {
    $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $title]);
    if (!$media) {
      throw new \Exception(sprintf('The media named "%s" does not exist', $title));
    }

    $media = reset($media);
    $media->delete();
  }

  /**
   * Enables the test module scanning.
   *
   * The AV Portal media mock is a test module so it cannot be enabled by
   * default as it is not being scanned. By changing the settings temporarily,
   * we can allow that to happen.
   */
  protected function enableTestModuleScanning(): void {
    $settings = Settings::getAll();
    $settings['extension_discovery_scan_tests'] = TRUE;
    // We just have to re-instantiate the singleton.
    new Settings($settings);
  }

}