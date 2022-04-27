import { API_URL, createFetcher } from 'automation/api';
import useSWRMutation from 'swr/mutation';

const url = `${API_URL}/workflows`;

type RequestData = {
  name: string;
  steps: Record<
    string,
    {
      id: string;
      type: string;
      next_step_id?: string;
      args?: Record<string, unknown>;
    }
  >;
};

type ResponseData = {
  data: null;
};

export const createWorkflow = createFetcher<ResponseData, RequestData>({
  method: 'POST',
});

export const useCreateWorkflowMutation = () =>
  useSWRMutation(url, createWorkflow);
