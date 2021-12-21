<?php

namespace MailPoet\Segments;

use MailPoet\ConflictException;
use MailPoet\Entities\SegmentEntity;
use MailPoet\Entities\SubscriberSegmentEntity;
use MailPoet\NotFoundException;
use MailPoetVendor\Doctrine\ORM\EntityManager;
use MailPoetVendor\Doctrine\ORM\ORMException;

class SegmentSaveController {
  /** @var SegmentsRepository */
  private $segmentsRepository;

  /** @var EntityManager */
  private $entityManager;

  public function __construct(
    SegmentsRepository $segmentsRepository,
    EntityManager $entityManager
  ) {
    $this->segmentsRepository = $segmentsRepository;
    $this->entityManager = $entityManager;
  }

  /**
   * @throws ConflictException
   * @throws NotFoundException
   * @throws ORMException
   */
  public function save(array $data = []): SegmentEntity {
    $id = isset($data['id']) ? (int)$data['id'] : null;
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';

    return $this->segmentsRepository->createOrUpdate($name, $description, SegmentEntity::TYPE_DEFAULT, [], $id);
  }

  /**
   * @throws ConflictException
   */
  public function duplicate(SegmentEntity $segmentEntity): SegmentEntity {
    $duplicate = clone $segmentEntity;
    $duplicate->setName(sprintf(__('Copy of %s', 'mailpoet'), $segmentEntity->getName()));
    $duplicateName = $duplicate->getName();

    if (!$this->segmentsRepository->isNameUnique($duplicate->getName(), null)) {
      $similarNamesWithNumbers = $this->segmentsRepository->getSegmentNamesLike($duplicateName . ' (%)');
      if (empty($similarNamesWithNumbers)) {
        $duplicate->setName($duplicateName . ' (1)');
      } else {
        $copyNumbers = array_map(function($name) use ($duplicateName) {
          $regex = "/$duplicateName \(([0-9]+)\)/";
          $matches = [];
          preg_match($regex, $name, $matches);
          return $matches[1];
        }, $similarNamesWithNumbers);
        $duplicate->setName(strtr(':original (:copyNumber)', [
          ':original' => $duplicateName,
          ':copyNumber' => max($copyNumbers) + 1,
        ]));
      }
    }

    $this->segmentsRepository->verifyNameIsUnique($duplicate->getName(), $duplicate->getId());

    $this->entityManager->transactional(function (EntityManager $entityManager) use ($duplicate, $segmentEntity) {
      $entityManager->persist($duplicate);
      $entityManager->flush();

      $subscriberSegmentTable = $entityManager->getClassMetadata(SubscriberSegmentEntity::class)->getTableName();
      $conn = $this->entityManager->getConnection();
      $stmt = $conn->prepare("
        INSERT INTO $subscriberSegmentTable (segment_id, subscriber_id, status, created_at)
        SELECT :duplicateId, subscriber_id, status, NOW()
        FROM $subscriberSegmentTable
        WHERE segment_id = :segmentId
      ");
      $stmt->executeQuery([
        'duplicateId' => $duplicate->getId(),
        'segmentId' => $segmentEntity->getId(),
      ]);
    });

    return $duplicate;
  }
}
