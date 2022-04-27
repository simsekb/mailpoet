import { api } from 'automation/config';

export const API_URL = `${api.root}/mailpoet/v1/automation`;

type RequestOptions<Arg> = Readonly<{
  arg: Arg;
}>;

export const createFetcher =
  <ResponseData, RequestData = unknown>(init?: RequestInit) =>
  async (
    info: RequestInfo,
    options: RequestOptions<RequestData>,
  ): Promise<ResponseData> => {
    const response = await fetch(info, {
      ...init,
      headers: {
        'content-type': 'application/json',
        'x-wp-nonce': api.nonce,
      },
      body: options?.arg ? JSON.stringify(options.arg) : undefined,
    });
    return response.json();
  };
