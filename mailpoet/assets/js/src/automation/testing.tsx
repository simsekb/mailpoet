import { id } from './id';
import { useCreateWorkflowMutation } from './data';

const createWaitStep = () => ({
  id: id(),
  type: 'action',
  key: 'core:wait',
  args: {
    seconds: 60,
  },
});

const createTrigger = (nextStepId: string) => ({
  id: id(),
  type: 'trigger',
  key: 'mailpoet:segment:subscribed',
  next_step_id: nextStepId,
});

const createWorkflowData = () => {
  const wait = createWaitStep();
  const trigger = createTrigger(wait.id);
  return {
    name: `Test ${new Date().toISOString()}`,
    steps: {
      [trigger.id]: trigger,
      [wait.id]: wait,
    },
  };
};

export function CreateTestingWorkflowButton(): JSX.Element {
  const {
    trigger: createWorkflow,
    error,
    isMutating,
  } = useCreateWorkflowMutation();

  return (
    <div>
      <button
        type="button"
        onClick={() => createWorkflow(createWorkflowData())}
        disabled={isMutating}
      >
        Create testing workflow
      </button>
      {error && (
        <div>{error?.data?.message ?? 'An unknown error occurred'}</div>
      )}
    </div>
  );
}
