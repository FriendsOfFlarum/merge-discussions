import app from 'flarum/forum/app';
import { override } from 'flarum/common/extend';
import RequestError from 'flarum/common/utils/RequestError';

type RequestErrorCatch = (error: unknown, ...rest: unknown[]) => Promise<never>;

/**
 * Follow a link into a merged-away discussion to wherever it went.
 *
 * Inside the forum, links load discussions through the API rather than the
 * forum route, so they never meet the server's redirect. The API's 404 for a
 * merged-away discussion says where the discussion, or the linked post, is now:
 * go there instead of showing "not found".
 */
export default function followMergeRedirects() {
  // requestErrorCatch is protected, but it is where every failed request lands.
  override(app as unknown as { requestErrorCatch: RequestErrorCatch }, 'requestErrorCatch', function (original, error, ...rest) {
    const url = mergeRedirect(error);

    if (!url) {
      return original(error, ...rest);
    }

    navigate(url);

    return Promise.reject(error);
  });
}

function mergeRedirect(error: unknown): string | null {
  if (!(error instanceof RequestError) || error.status !== 404) {
    return null;
  }

  const meta = error.response?.meta as { 'fof-merge-discussions'?: { redirect?: unknown } } | undefined;
  const redirect = meta?.['fof-merge-discussions']?.redirect;

  return typeof redirect === 'string' ? redirect : null;
}

function navigate(url: string) {
  const target = new URL(url, document.baseURI);

  // A forum reached under another address than its configured one cannot
  // route there itself.
  if (target.origin !== window.location.origin) {
    window.location.replace(target.href);

    return;
  }

  // Forum routes include the base path, as the URL's path does. Replace the
  // dead URL in the history, so going back does not land on it again.
  m.route.set(target.pathname + target.search + target.hash, undefined, { replace: true });
}
