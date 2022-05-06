<?php

namespace MailPoet\Test\Cron;

use Codeception\Stub;
use MailPoet\Cron\CronHelper;
use MailPoet\Cron\CronTrigger;
use MailPoet\Cron\DaemonWpCronRunner;
use MailPoet\Cron\Triggers\MailPoet;
use MailPoet\Cron\Triggers\WordPress;
use MailPoet\Settings\SettingsController;
use MailPoet\WP\Functions as WPFunctions;

class CronTriggerTest extends \MailPoetUnitTest {
  public function testItDefinesConstants() {
    expect(CronTrigger::METHOD_LINUX_CRON)->same('Linux Cron');
    expect(CronTrigger::METHOD_MAILPOET)->same('MailPoet');
    expect(CronTrigger::METHOD_WORDPRESS)->same('WordPress');
    expect(CronTrigger::METHODS)->equals([
      'mailpoet' => 'MailPoet',
      'wordpress' => 'WordPress',
      'linux_cron' => 'Linux Cron',
      'none' => 'Disabled',
    ]);
    expect(CronTrigger::DEFAULT_METHOD)->equals('WordPress');
    expect(CronTrigger::SETTING_NAME)->equals('cron_trigger');
  }

  public function testItCanInitializeCronTriggerMethod() {
    $settingsMock = Stub::makeEmpty(SettingsController::class, [
      'get' => CronTrigger::METHOD_WORDPRESS,
    ]);
    $cronTrigger = $this->createCronTrigger($settingsMock);
    expect($cronTrigger->init())->true();
  }

  public function testItReturnsFalseWhenItCantInitializeCronTriggerMethod() {
    $settingsMock = Stub::makeEmpty(SettingsController::class, [
      'get' => 'unknown-method',
    ]);
    $cronTrigger = $this->createCronTrigger($settingsMock);
    expect($cronTrigger->init())->false();
  }

  public function testItIgnoresExceptionsThrownFromCronTriggerMethods() {
    $settingsMock = Stub::makeEmpty(SettingsController::class, [
      'get' => CronTrigger::METHOD_MAILPOET,
    ]);
    $mailpoetTrigger = $this->makeEmpty(MailPoet::class, [
      'run' => function () {
        throw new \Exception();
      },
    ]);
    $cronTrigger = $this->createCronTrigger($settingsMock, $mailpoetTrigger);
    expect($cronTrigger->init())->null();
  }

  private function createCronTrigger(
    SettingsController $settings,
    MailPoet $mailpoetTrigger = null,
    WordPress $wordpressTrigger = null,
    WPFunctions $wp = null,
    DaemonWpCronRunner $wpCronRunner = null
  ) {
    $mailpoetTrigger = $mailpoetTrigger ?: $this->make(MailPoet::class, ['run' => true]);
    $wordpressTrigger = $wordpressTrigger ?: $this->make(WordPress::class, ['run' => true]);
    $wp = $wp ?: $this->make(WPFunctions::class, ['addAction' => true]);
    $wpCronRunner = $wpCronRunner ?: $this->make(DaemonWpCronRunner::class, []);
    return new CronTrigger($mailpoetTrigger, $wordpressTrigger, $settings, $wp, $wpCronRunner);
  }
}
