import { API_URL, createFetcher } from 'automation/api';
import useSWRMutation from 'swr/mutation';

const url = `${API_URL}/system/database`;

export const deleteDatabase = createFetcher({
  method: 'DELETE',
});

export const useDeleteDatabaseMutation = () =>
  useSWRMutation(url, deleteDatabase);
