<?php

namespace MailPoet\Test\Automation\Integrations\MailPoet\Actions\SendWelcomeEmail;

use MailPoet\Automation\Engine\Workflows\Step;
use MailPoet\Automation\Engine\Workflows\Workflow;
use MailPoet\Automation\Engine\Workflows\WorkflowRun;
use MailPoet\Automation\Integrations\MailPoet\Actions\SendWelcomeEmailAction;
use MailPoet\Automation\Integrations\MailPoet\Subjects\SegmentSubject;
use MailPoet\Automation\Integrations\MailPoet\Subjects\SubscriberSubject;
use MailPoet\DI\ContainerWrapper;
use MailPoet\Entities\NewsletterEntity;
use MailPoet\Entities\SegmentEntity;
use MailPoet\Entities\SubscriberEntity;
use MailPoet\Exception;
use MailPoet\InvalidStateException;
use MailPoet\Newsletter\Sending\ScheduledTasksRepository;
use MailPoet\Segments\SegmentsRepository;
use MailPoet\Subscribers\SubscribersRepository;
use MailPoet\Test\DataFactories\Newsletter;
use MailPoet\Test\DataFactories\Segment;
use MailPoet\Test\DataFactories\Subscriber;

class SendWelcomeEmailActionTest extends \MailPoetTest {

  /** @var ScheduledTasksRepository */
  private $scheduledTasksRepository;

  /** @var SubscribersRepository */
  private $subscribersRepository;

  /** @var SegmentsRepository */
  private $segmentsRepository;

  /** @var SendWelcomeEmailAction */
  private $action;

  /** @var SubscriberSubject */
  private $subscriberSubject;

  /** @var SegmentSubject */
  private $segmentSubject;

  /** @var Step */
  private $step;

  /** @var Workflow */
  private $workflow;

  /** @var SegmentEntity */
  private $segment;

  /** @var NewsletterEntity */
  private $welcomeEmail;

  public function _before() {
    parent::_before();
    $this->scheduledTasksRepository = $this->diContainer->get(ScheduledTasksRepository::class);
    $this->segmentsRepository = $this->diContainer->get(SegmentsRepository::class);
    $this->subscribersRepository = $this->diContainer->get(SubscribersRepository::class);
    $this->action = $this->diContainer->get(SendWelcomeEmailAction::class);
    $this->subscriberSubject = $this->diContainer->get(SubscriberSubject::class);
    $this->segmentSubject = $this->diContainer->get(SegmentSubject::class);

    $this->segment = (new Segment())->create();
    $this->welcomeEmail = (new Newsletter())->withWelcomeTypeForSegment($this->segment->getId())->create();
    $this->step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $this->welcomeEmail->getId()]);
    $this->workflow = new Workflow('test-workflow', []);
  }

  public function testItKnowsWhenItHasAllRequiredSubjects() {
    expect($this->action->isValid([], $this->step, $this->workflow))->false();
    expect($this->action->isValid($this->getSubjects(), $this->step, $this->workflow))->true();
  }

  public function testItRequiresASubscriberSubject() {
    $subjects = $this->getSubjects();
    unset($subjects[$this->subscriberSubject->getKey()]);
    expect($this->action->isValid($subjects, $this->step, $this->workflow))->false();
  }

  public function testItRequiresASegmentSubject() {
    $subjects = $this->getSubjects();
    unset($subjects[$this->segmentSubject->getKey()]);
    expect($this->action->isValid($subjects, $this->step, $this->workflow))->false();
  }

  public function testItIsNotValidIfStepHasNoWelcomeEmail(): void {
    $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, []);
    expect($this->action->isValid($this->getSubjects(), $step, $this->workflow))->false();
  }

  public function testItRequiresAWelcomeEmailType(): void {
    $newsletter = (new Newsletter())->withPostNotificationsType()->create();
    $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $newsletter->getId()]);
    expect($this->action->isValid($this->getSubjects(), $step, $this->workflow))->false();
  }

  public function testHappyPath() {
    $segment = (new Segment())->create();
    $subscriber = (new Subscriber())
      ->withStatus(SubscriberEntity::STATUS_SUBSCRIBED)
      ->withSegments([$segment])
      ->create();
    $subjects = $this->getLoadedSubjects($subscriber, $segment);
    $welcomeEmail = (new Newsletter())->withWelcomeTypeForSegment($segment->getId())->create();

    $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $welcomeEmail->getId()]);
    $workflow = new Workflow('some-workflow', [$step]);
    $run = new WorkflowRun(1, 'trigger-key', $subjects);

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);

    $this->action->run($workflow, $run, $step);

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(1);
  }

  public function testItDoesNotScheduleDuplicates(): void {
    $segment = (new Segment())->create();
    $subscriber = (new Subscriber())
      ->withStatus(SubscriberEntity::STATUS_SUBSCRIBED)
      ->withSegments([$segment])
      ->create();
    $subjects = $this->getLoadedSubjects($subscriber, $segment);
    $welcomeEmail = (new Newsletter())->withWelcomeTypeForSegment($segment->getId())->create();

    $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $welcomeEmail->getId()]);
    $workflow = new Workflow('some-workflow', [$step]);
    $run = new WorkflowRun(1, 'trigger-key', $subjects);

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);

    $action = ContainerWrapper::getInstance()->get(SendWelcomeEmailAction::class);
    $action->run($workflow, $run, $step);

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(1);

    try {
      $action->run($workflow, $run, $step);
    } catch (InvalidStateException $exception) {
      // The exception itself isn't as important as the outcome
    }

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(1);
  }

  public function testNothingScheduledIfSegmentDeleted(): void {
    $segment = (new Segment())->create();
    $subscriber = (new Subscriber())
      ->withStatus(SubscriberEntity::STATUS_SUBSCRIBED)
      ->withSegments([$segment])
      ->create();
    $subjects = $this->getLoadedSubjects($subscriber, $segment);
    $welcomeEmail = (new Newsletter())->withWelcomeTypeForSegment($segment->getId())->create();

    $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $welcomeEmail->getId()]);
    $workflow = new Workflow('some-workflow', [$step]);
    $run = new WorkflowRun(1, 'trigger-key', $subjects);

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);

    $this->segmentsRepository->bulkDelete([$segment->getId()]);
    $action = ContainerWrapper::getInstance()->get(SendWelcomeEmailAction::class);

    try {
      $action->run($workflow, $run, $step);
    } catch (Exception $exception) {
      // The exception itself isn't as important as the outcome
    }

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);
  }

  public function testNothingScheduledIfSubscriberDeleted(): void {
    $segment = (new Segment())->create();
    $subscriber = (new Subscriber())
      ->withStatus(SubscriberEntity::STATUS_SUBSCRIBED)
      ->withSegments([$segment])
      ->create();
    $subjects = $this->getLoadedSubjects($subscriber, $segment);
    $welcomeEmail = (new Newsletter())->withWelcomeTypeForSegment($segment->getId())->create();

    $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $welcomeEmail->getId()]);
    $workflow = new Workflow('some-workflow', [$step]);
    $run = new WorkflowRun(1, 'trigger-key', $subjects);

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);

    $this->subscribersRepository->bulkDelete([$subscriber->getId()]);
    $action = ContainerWrapper::getInstance()->get(SendWelcomeEmailAction::class);

    try {
      $action->run($workflow, $run, $step);
    } catch (Exception $exception) {
      // The exception itself isn't as important as the outcome
    }

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);
  }

  public function testNothingScheduledIfSubscriberIsNotGloballySubscribed(): void {
    $segment = (new Segment())->create();

    $otherStatuses = [
      SubscriberEntity::STATUS_UNCONFIRMED,
      SubscriberEntity::STATUS_INACTIVE,
      SubscriberEntity::STATUS_BOUNCED,
      SubscriberEntity::STATUS_UNSUBSCRIBED,
    ];

    foreach ($otherStatuses as $status) {
      $subscriber = (new Subscriber())
        ->withStatus($status)
        ->withSegments([$segment])
        ->create();
      $subjects = $this->getLoadedSubjects($subscriber, $segment);
      $welcomeEmail = (new Newsletter())->withWelcomeTypeForSegment($segment->getId())->create();

      $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $welcomeEmail->getId()]);
      $workflow = new Workflow('some-workflow', [$step]);
      $run = new WorkflowRun(1, 'trigger-key', $subjects);

      $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
      expect($scheduled)->count(0);

      $this->subscribersRepository->bulkDelete([$subscriber->getId()]);
      $action = ContainerWrapper::getInstance()->get(SendWelcomeEmailAction::class);

      try {
        $action->run($workflow, $run, $step);
      } catch (Exception $exception) {
        // The exception itself isn't as important as the outcome
      }

      $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
      expect($scheduled)->count(0);
    }
  }

  public function testNothingScheduledIfSubscriberNotSubscribedToSegment(): void {
    $segment = (new Segment())->create();
    $subscriber = (new Subscriber())
      ->withStatus(SubscriberEntity::STATUS_SUBSCRIBED)
      ->create();
    $subjects = $this->getLoadedSubjects($subscriber, $segment);
    $welcomeEmail = (new Newsletter())->withWelcomeTypeForSegment($segment->getId())->create();

    $step = new Step('step-id', Step::TYPE_ACTION, 'step-key', null, ['welcomeEmailId' => $welcomeEmail->getId()]);
    $workflow = new Workflow('some-workflow', [$step]);
    $run = new WorkflowRun(1, 'trigger-key', $subjects);

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);

    $action = ContainerWrapper::getInstance()->get(SendWelcomeEmailAction::class);

    try {
      $action->run($workflow, $run, $step);
    } catch (Exception $exception) {
      // The exception itself isn't as important as the outcome
    }

    $scheduled = $this->scheduledTasksRepository->findByNewsletterAndSubscriberId($welcomeEmail, (int)$subscriber->getId());
    expect($scheduled)->count(0);
  }

  private function getLoadedSubscriberSubject(SubscriberEntity $subscriber): SubscriberSubject {
    $subscriberSubject = $this->diContainer->get(SubscriberSubject::class);
    $subscriberSubject->load(['subscriber_id' => $subscriber->getId()]);

    return $subscriberSubject;
  }

  private function getLoadedSegmentSubject(SegmentEntity $segment): SegmentSubject {
    $segmentSubject = $this->diContainer->get(SegmentSubject::class);
    $segmentSubject->load(['segment_id' => $segment->getId()]);

    return $segmentSubject;
  }

  private function getSubjects(): array {
    return [
      $this->segmentSubject->getKey() => $this->segmentSubject,
      $this->subscriberSubject->getKey() => $this->subscriberSubject,
    ];
  }

  private function getLoadedSubjects(SubscriberEntity $subscriber, SegmentEntity $segment): array {
    return [
      $this->subscriberSubject->getKey() => $this->getLoadedSubscriberSubject($subscriber),
      $this->segmentSubject->getKey() => $this->getLoadedSegmentSubject($segment),
    ];
  }
}
