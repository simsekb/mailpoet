import ReactDOM from 'react-dom';
import { SWRConfig } from 'swr';
import { CreateTestingWorkflowButton } from './testing';
import {
  useCreateDatabaseMutation,
  useDeleteDatabaseMutation,
  useWorkflowsQuery,
} from './data';

function Workflows(): JSX.Element {
  const { data, isLoading } = useWorkflowsQuery();

  if (!data || isLoading) {
    return <div>Loading workflows...</div>;
  }

  return (
    <div>
      {data.data.map((workflow) => (
        <div>
          [{workflow.id}] {workflow.name} ({workflow.status})
        </div>
      ))}
    </div>
  );
}

function RecreateSchemaButton(): JSX.Element {
  const {
    trigger: createSchema,
    error,
    isMutating,
  } = useCreateDatabaseMutation();

  return (
    <div>
      <button
        type="button"
        onClick={() => createSchema()}
        disabled={isMutating}
      >
        Recreate DB schema (data will be lost)
      </button>
      {error && (
        <div>{error?.data?.message ?? 'An unknown error occurred'}</div>
      )}
    </div>
  );
}

function DeleteSchemaButton(): JSX.Element {
  const {
    trigger: deleteSchema,
    error,
    isMutating,
  } = useDeleteDatabaseMutation();

  return (
    <div>
      <button
        type="button"
        onClick={async () => {
          await deleteSchema();
          window.location.href =
            '/wp-admin/admin.php?page=mailpoet-experimental';
        }}
        disabled={isMutating}
      >
        Delete DB schema & deactivate feature
      </button>
      {error && (
        <div>{error?.data?.message ?? 'An unknown error occurred'}</div>
      )}
    </div>
  );
}

function App(): JSX.Element {
  return (
    <SWRConfig
      value={{
        focusThrottleInterval: 60000,
      }}
    >
      <div>
        <CreateTestingWorkflowButton />
        <RecreateSchemaButton />
        <DeleteSchemaButton />
        <Workflows />
      </div>
    </SWRConfig>
  );
}

window.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('mailpoet_automation');
  if (root) {
    ReactDOM.render(<App />, root);
  }
});
