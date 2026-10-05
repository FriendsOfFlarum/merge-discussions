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
    const route = mergeRedirectRoute(error);

    if (route === null) {
      return original(error, ...rest);
    }

    // Replace the dead URL in the history, so going back does not land on it again.
    m.route.set(route, undefined, { replace: true });

    return Promise.reject(error);
  });
}

/**
 * The route to follow when the error is the API saying a discussion was merged
 * away, or null to handle the error as usual.
 */
function mergeRedirectRoute(error: unknown): string | null {
  if (!(error instanceof RequestError) || error.status !== 404) {
    return null;
  }

  const meta = error.response?.meta as { 'fof-merge-discussions'?: { redirect?: unknown } } | undefined;
  const redirect = meta?.['fof-merge-discussions']?.redirect;

  if (typeof redirect !== 'string') {
    return null;
  }

  let target: URL;

  // Throwing here would not reach the caller: override() swallows it, and the
  // failed request would resolve with nothing instead of rejecting.
  try {
    target = new URL(redirect, document.baseURI);
  } catch {
    return null;
  }

  // Only ever a page of this forum: the redirect comes from a response, and
  // nothing else is ours to navigate to.
  if (target.origin !== window.location.origin) {
    return null;
  }

  // Forum routes include the base path, as the URL's path does.
  return target.pathname + target.search + target.hash;
}
