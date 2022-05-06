<?php

namespace MailPoet\Cron;

use MailPoet\Cron\Triggers\WordPress;
use MailPoet\WP\Functions as WPFunctions;

class DaemonWpCronRunner {
  /** @var CronHelper */
  private $cronHelper;

  /** @var WordPress */
  private $wordpressTrigger;

  /** @var string */
  private $token;

  /** @var WPFunctions */
  private $wp;

  /** @var Daemon */
  private $daemon;

  public function __construct(
    CronHelper $cronHelper,
    WordPress $wordpressTrigger,
    Daemon $daemon,
    WPFunctions $wp
  ) {
    $this->cronHelper = $cronHelper;
    $this->token = $this->cronHelper->createToken();
    $this->wordpressTrigger = $wordpressTrigger;
    $this->daemon = $daemon;
    $this->wp = $wp;
  }

  public function run($token) {
    ignore_user_abort(true);
    if (strpos((string)@ini_get('disable_functions'), 'set_time_limit') === false) {
      set_time_limit(0);
    }

    $settingsDaemonData = $this->cronHelper->getDaemon();

    // Daemon doesn't exists
    if (empty($settingsDaemonData)) {
      return;
    }

    // Tokens don't match the cron was triggered for non exiting daemon
    if (!$token || $token !== $settingsDaemonData['token']) {
      return;
    }

    // Set own token so that no one can reuse the original token for invoking the cron again
    $settingsDaemonData['token'] = $this->token;

    $this->daemon->run($settingsDaemonData);

    // refresh execution data
    $settingsDaemonData = $this->cronHelper->getDaemon();

    // Have more jobs to do and token is still valid schedule next run
    if ($this->wordpressTrigger->checkExecutionRequirements() && ($settingsDaemonData && $settingsDaemonData['token'] === $this->token)) {
      $this->wp->wpUnscheduleHook('mailpoet_start_daemon_http_runner');
      $secondAgo = $this->wp->currentTime('timestamp') - 1;
      $this->wp->wpScheduleSingleEvent($secondAgo, 'mailpoet_start_daemon_http_runner', [$this->token]);
    }
  }
}
