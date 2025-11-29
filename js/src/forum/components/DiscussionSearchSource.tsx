import app from 'flarum/forum/app';
import highlight from 'flarum/common/helpers/highlight';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

export default class DiscussionSearchSource {
  protected results: Map<string, Discussion[]> = new Map();
  protected onSelect: (discussion: Discussion) => void;
  protected ignore: string;

  constructor(onSelect: (discussion: Discussion) => void, ignore: string) {
    this.results = new Map();

    this.onSelect = onSelect;
    this.ignore = ignore;
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

    if (!Number.isNaN(id) && idStr !== this.ignore) {
      return app.store
        .find<Discussion>('discussions', idStr)
        .then((d) => {
          this.results.set(query, [d]);
        })
        .catch(() => {});
    }

    return app.store.find<Discussion[]>('discussions', params).then((results) => {
      this.results.set(
        query,
        results.filter((d) => d.id() !== this.ignore)
      );
    });
  }

  view(query: string): Mithril.Vnode[] {
    query = query.toLowerCase();

    const results = this.results.get(query) || [];

    return results.map((discussion) => {
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
