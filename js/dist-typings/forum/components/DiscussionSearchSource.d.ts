import { SearchSource } from 'flarum/forum/components/Search';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
export default class DiscussionSearchSource implements SearchSource {
    protected results: Map<string, Discussion[]>;
    protected ignore: (discussion: Discussion) => boolean;
    protected onSelect: (discussion: Discussion) => void;
    protected minSearchLength: number;
    constructor(onSelect: (discussion: Discussion) => void, ignore?: (discussion: Discussion) => boolean, minSearchLength?: number);
    search(query: string): Promise<void>;
    view(query: string): Mithril.Vnode[];
}
