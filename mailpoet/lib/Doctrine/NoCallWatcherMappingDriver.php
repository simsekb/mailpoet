<?php declare(strict_types=1);

namespace MailPoet\Doctrine;

use MailPoetVendor\Doctrine\Persistence\Mapping\ClassMetadata;
use MailPoetVendor\Doctrine\Persistence\Mapping\Driver\MappingDriver;

/**
 * This driver is intended for usage in production mode.
 * We rely on cached metadata in production mode, and the driver should never be called.
 * It throws exceptions so that we can catch that it was called e.g. in acceptance test.
 */
class NoCallWatcherMappingDriver implements MappingDriver {
  public function loadMetadataForClass($className, ClassMetadata $metadata) {
    throw new \Exception('MappingDriver::loadMetadataForClass should not be called!');
  }

  public function getAllClassNames() {
    throw new \Exception('MappingDriver::getAllClassNames should not be called!');
  }

  public function isTransient($className) {
    throw new \Exception('MappingDriver::isTransient should not be called!');
  }
}
