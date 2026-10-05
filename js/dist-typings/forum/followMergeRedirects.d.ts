/**
 * Follow a link into a merged-away discussion to wherever it went.
 *
 * Inside the forum, links load discussions through the API rather than the
 * forum route, so they never meet the server's redirect. The API's 404 for a
 * merged-away discussion says where the discussion, or the linked post, is now:
 * go there instead of showing "not found".
 */
export default function followMergeRedirects(): void;
