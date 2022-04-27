import { API_URL, createFetcher } from 'automation/api';
import useSWR from 'swr';

const url = `${API_URL}/workflows`;

type ResponseData = {
  data: {
    id: string;
    name: string;
    status: 'inactive' | 'active';
    created_at: string;
    updated_at: string;
  }[];
};

export const getWorkflows = createFetcher<ResponseData>();

export const useWorkflowsQuery = () => useSWR(url, getWorkflows);
