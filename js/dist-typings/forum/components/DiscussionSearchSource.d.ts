import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
export default class DiscussionSearchSource {
    protected results: Map<string, Discussion[]>;
    protected onSelect: (discussion: Discussion) => void;
    protected ignore: string;
    constructor(onSelect: (discussion: Discussion) => void, ignore: string);
    search(query: string): Promise<void>;
    view(query: string): Mithril.Vnode[];
}
