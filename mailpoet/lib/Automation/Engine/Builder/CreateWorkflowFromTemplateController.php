<?php declare(strict_types = 1);

namespace MailPoet\Automation\Engine\Builder;

use MailPoet\Automation\Engine\Storage\WorkflowStorage;
use MailPoet\Automation\Engine\Workflows\Workflow;
use MailPoet\Automation\Integrations\MailPoet\Templates\WorkflowBuilder;
use MailPoet\UnexpectedValueException;

class CreateWorkflowFromTemplateController {
  /** @var WorkflowStorage */
  private $storage;
  
  /** @var WorkflowBuilder */
  private $templates;

  public function __construct(
    WorkflowStorage $storage,
    WorkflowBuilder $templates
  ) {
    $this->storage = $storage;
    $this->templates = $templates;
  }

  public function createWorkflow(array $data): Workflow {
    $name = $data['name'];
    $template = $data['template'];

    switch ($template) {
      case 'delayed-email-after-signup':
        $workflow = $this->templates->delayedEmailAfterSignupWorkflow($name);
        break;
      default:
        throw UnexpectedValueException::create()->withMessage('Template not found.');
    }

    $this->storage->createWorkflow($workflow);
    return $workflow;
  }
}
