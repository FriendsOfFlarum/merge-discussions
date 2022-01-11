import app from 'flarum/forum/app';
import highlight from 'flarum/common/helpers/highlight';

export default class DiscussionSearchSource {
  constructor(onSelect, ignore) {
    this.results = new Map();

    this.onSelect = onSelect;
    this.ignore = ignore;
  }

  search(query) {
    query = query.toLowerCase();
    const limit = app.forum.attribute('fof-merge-discussions.search_limit') || 4;

    this.results.set(query, []);

    const params = {
      filter: { q: query },
      page: { limit },
    };

    const id = Number(query);

    if (!Number.isNaN(id) && id !== this.ignore) {
      return app.store
        .find('discussions', id)
        .then((d) => {
          this.results.set(query, [d]);
        })
        .catch(() => []);
    }

    return app.store.find('discussions', params).then((results) => {
      this.results.set(
        query,
        results.filter((d) => d.id() !== this.ignore)
      );
    });
  }

  view(query) {
    query = query.toLowerCase();

    const results = this.results.get(query) || [];

    return [
      results.map((discussion) => {
        return (
          <li className="DiscussionSearchResult" data-index={'discussions' + discussion.id()}>
            <button className='Button--ua-reset' type='button' onclick={(e) => {e.stopPropagation(); this.onSelect(discussion);}}>
              <div className="DiscussionSearchResult-title">
                <i>{highlight(discussion.id(), query)}</i> ~ {highlight(discussion.title(), query)}
              </div>
            </button>
          </li>
        );
      }),
    ];
  }
}
