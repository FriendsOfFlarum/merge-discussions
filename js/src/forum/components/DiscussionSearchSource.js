import highlight from 'flarum/helpers/highlight';

export default class DiscussionSearchSource {
    constructor(onSelect, ignore) {
        this.results = {};

        this.onSelect = onSelect;
        this.ignore = ignore;
    }

    search(query) {
        query = query.toLowerCase();

        this.results[query] = [];

        const params = {
            filter: {q: query},
            page: {limit: 3},
        };

        return app.store.find('discussions', params).then(results => {
            this.results[query] = results.filter(d => d.id() !== this.ignore);
        });
    }

    view(query) {
        query = query.toLowerCase();

        const results = this.results[query] || [];

        return [
            results.map(discussion => {
                return (
                    <li className="DiscussionSearchResult" data-index={'discussions' + discussion.id()}>
                        <a onclick={() => this.onSelect(discussion)}>
                            <div className="DiscussionSearchResult-title">{highlight(discussion.title(), query)}</div>
                        </a>
                    </li>
                );
            })
        ];
    }
}