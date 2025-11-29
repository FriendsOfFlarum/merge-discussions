import Search from 'flarum/forum/components/Search';
import ItemList from 'flarum/common/utils/ItemList';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
export interface DiscussionSearchAttrs extends Mithril.Attributes {
    state: any;
    onSelect: (discussion: Discussion) => void;
    ignore: string;
}
export default class DiscussionSearch extends Search<DiscussionSearchAttrs> {
    oncreate(vnode: Mithril.VnodeDOM<DiscussionSearchAttrs, this>): void;
    view(): Mithril.Vnode;
    findClearButton(vnode: any): any;
    findInput(vnode: any): any;
    updateMaxHeight(): void;
    sourceItems(): ItemList<any>;
}
