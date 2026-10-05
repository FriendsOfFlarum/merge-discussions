import { jest, describe, it, expect, beforeAll, beforeEach, afterEach } from '@jest/globals';
import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import app from 'flarum/forum/app';
import RequestError from 'flarum/common/utils/RequestError';
import m from 'mithril';
import '../../src/forum';

/**
 * Following a link inside the forum loads the discussion through the API, so a
 * link into a merged-away discussion never meets the server's redirect. The
 * API's 404 says where the discussion, or the linked post, went instead
 * (#34); the forum should go there rather than show "not found".
 */

function notFound(meta?: Record<string, unknown>): RequestError {
  const body = { errors: [{ status: '404', code: 'not_found' }], ...(meta ? { meta } : {}) };

  return new RequestError(404, JSON.stringify(body), { method: 'GET', url: 'http://localhost/api/discussions/2-source' } as any, {} as XMLHttpRequest);
}

function mergedInto(url: string): RequestError {
  return notFound({ 'fof-merge-discussions': { redirect: url } });
}

/**
 * Where every failed request lands. It is protected, so reached around the types.
 */
function fail(error: RequestError): Promise<unknown> {
  return (app as any).requestErrorCatch(error);
}

beforeAll(() => {
  bootstrapForum();
  app.boot();
});

let routeSet: ReturnType<typeof jest.spyOn>;
let showAlert: ReturnType<typeof jest.spyOn>;

beforeEach(() => {
  routeSet = jest.spyOn(m.route, 'set').mockImplementation(() => {});
  showAlert = jest.spyOn(app.alerts, 'show');
});

afterEach(() => {
  jest.restoreAllMocks();
});

describe('a link into a merged-away discussion', () => {
  it('goes where the discussion went, in place of the dead URL', async () => {
    await expect(fail(mergedInto('http://localhost/d/1-target/4'))).rejects.toBeInstanceOf(RequestError);

    expect(routeSet).toHaveBeenCalledWith('/d/1-target/4', undefined, { replace: true });
  });

  it('shows no "not found" alert on the way', async () => {
    await expect(fail(mergedInto('http://localhost/d/1-target/4'))).rejects.toBeInstanceOf(RequestError);

    expect(showAlert).not.toHaveBeenCalled();
  });
});

describe('any other not found', () => {
  it('still shows the usual alert, and goes nowhere', async () => {
    await expect(fail(notFound())).rejects.toBeInstanceOf(RequestError);

    expect(showAlert).toHaveBeenCalled();
    expect(routeSet).not.toHaveBeenCalled();
  });
});
