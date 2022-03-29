<?php

namespace MailPoet\Doctrine;

use MailPoet\Config\Env;
use MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata;
use MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadataFactory;
use MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadataInfo;

// Taken from Doctrine docs (see link bellow) but implemented in metadata factory instead of an event
// because we need to add prefix at runtime, not at metadata dump (which is included in builds).
// @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.5/cookbook/sql-table-prefixes.html
class TablePrefixMetadataFactory extends ClassMetadataFactory {
  /** @var string */
  private $prefix;

  /** @var array */
  private $prefixedMap = [];

  public function __construct() {
    if (Env::$dbPrefix === null) {
      throw new \RuntimeException('DB table prefix not initialized');
    }
    $this->prefix = Env::$dbPrefix;
    $this->setProxyClassNameResolver(new ProxyClassNameResolver());
  }

  /**
   * @return ClassMetadata<object>
   */
  public function getMetadataFor($className) {
    $classMetadata = parent::getMetadataFor($className);
    if (isset($this->prefixedMap[$classMetadata->getName()])) {
      return $classMetadata;
    }

    // prefix tables only after they are saved to cache so the prefix does not get included in cache
    // (getMetadataFor can call itself recursively but it saves to cache only after the recursive calls)
    $isCached = $this->isCached($classMetadata->getName());
    if ($classMetadata instanceof ClassMetadata && $isCached) {
      $this->addPrefix($classMetadata);
      $this->prefixedMap[$classMetadata->getName()] = true;
    }
    return $classMetadata;
  }

  /**
   * @param ClassMetadata<object> $classMetadata
   */
  public function addPrefix(ClassMetadata $classMetadata) {
    if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {
      $classMetadata->setPrimaryTable([
        'name' => $this->prefix . $classMetadata->getTableName(),
      ]);
    }

    foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
      if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
        $mappedTableName = $mapping['joinTable']['name'];
        $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
      }
    }
  }

  /**
   * @inerhitDoc
   */
  public function isTransient($className) {
    if (!$this->initialized) {
      $this->initialize();
    }
    $driver = $this->getDriver();
    if ($driver instanceof NoCallWatcherMappingDriver) {
      // Everything in cache are metadata and class with metadata is non-transient
      // See https://github.com/doctrine/persistence/blob/b07e347a24e7a19a2b6462e00a6dff899e4c2dd2/src/Persistence/Mapping/Driver/MappingDriver.php#L34
      return !$this->isCached($className);
    }
    return parent::isTransient($className);
  }

  private function isCached(string $className): bool {
    $cacheKey = $this->getCacheKey($className);
    return ($cache = $this->getCache()) ? $cache->hasItem($cacheKey) : false;
  }
}
