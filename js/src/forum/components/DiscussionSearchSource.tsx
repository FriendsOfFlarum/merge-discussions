import app from 'flarum/forum/app';
import { SearchSource } from 'flarum/forum/components/Search';
import highlight from 'flarum/common/helpers/highlight';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

export default class DiscussionSearchSource implements SearchSource {
  protected results: Map<string, Discussion[]> = new Map();
  protected ignore: (discussion: Discussion) => boolean;
  protected onSelect: (discussion: Discussion) => void;
  protected minSearchLength = 3;

  constructor(onSelect: (discussion: Discussion) => void, ignore?: (discussion: Discussion) => boolean, minSearchLength?: number) {
    this.results = new Map();

    this.onSelect = onSelect;
    this.ignore = ignore ?? (() => false);
    this.minSearchLength = minSearchLength || this.minSearchLength;
  }

  search(query: string): Promise<void> {
    query = query.toLowerCase();
    const limit = (app.forum.attribute('fof-merge-discussions.search_limit') as number) || 4;

    this.results.set(query, []);

    const params = {
      filter: { q: query },
      page: { limit },
    };

    const id = Number(query);
    const idStr = String(id);

    if (!Number.isNaN(id) && id > 0) {
      return app.store
        .find<Discussion>('discussions', idStr)
        .then((d) => {
          this.results.set(query, [d]);
        })
        .catch(() => {});
    }

    if (query.length < this.minSearchLength) {
      return Promise.resolve();
    }

    return app.store.find<Discussion[]>('discussions', params).then((results) => {
      this.results.set(query, results);
    });
  }

  view(query: string): Mithril.Vnode[] {
    query = query.toLowerCase();

    const results = this.results.get(query) || [];

    // Remove discussions that should be ignored (e.g. merge from/to target, discussions already selected).
    // We do this in view instead of search to prevent issues from caching modified results.
    const filteredResults = results.filter((discussion) => !this.ignore(discussion));

    if (!filteredResults.length) {
      return [
        <li className="DiscussionSearchResult DiscussionSearchResult--noResults">{app.translator.trans('core.lib.search.no_results_text')}</li>,
      ];
    }

    return filteredResults.map((discussion) => {
      const discussionId = discussion.id() || '';
      return (
        <li className="DiscussionSearchResult" data-index={'discussions' + discussionId} data-id={discussionId}>
          <button
            className="Button--ua-reset"
            type="button"
            onclick={(e: MouseEvent) => {
              e.stopPropagation();
              this.onSelect(discussion);
            }}
          >
            <div className="DiscussionSearchResult-title">
              <i>{highlight(discussionId, query)}</i> ~ {highlight(discussion.title(), query)}
            </div>
          </button>
        </li>
      );
    });
  }
}
