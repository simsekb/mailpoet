import { API_URL, createFetcher } from 'automation/api';
import useSWR from 'swr';

const url = `${API_URL}/workflows`;

export const getWorkflows = createFetcher();

export const useWorkflowsQuery = () => useSWR(url, getWorkflows);
