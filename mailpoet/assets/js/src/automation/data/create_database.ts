import { API_URL, createFetcher } from 'automation/api';
import useSWRMutation from 'swr/mutation';

const url = `${API_URL}/system/database`;

export const createDatabase = createFetcher({
  method: 'POST',
});

export const useCreateDatabaseMutation = () =>
  useSWRMutation(url, createDatabase);
