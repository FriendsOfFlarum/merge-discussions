import FormModal from 'flarum/common/components/FormModal';
import SearchState from 'flarum/common/states/SearchState';
import Stream from 'flarum/common/utils/Stream';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';
import ItemList from 'flarum/common/utils/ItemList';
export interface DiscussionMergeModalAttrs {
    discussion: Discussion;
    preselect?: Discussion;
}
export default class DiscussionMergeModal extends FormModal<any> {
    discussion: Discussion;
    type: Stream<string>;
    order: Stream<string>;
    merging: Discussion[];
    results: any[];
    preview: any;
    loadingPreview: boolean;
    searchState: SearchState;
    PostStream: any;
    oninit(vnode: Mithril.Vnode<any, this>): void;
    onready(): void;
    title(): string | any[];
    className(): string;
    types(): string[];
    ordering(): string[];
    typeItems(): ItemList<unknown>;
    orderItems(): ItemList<unknown>;
    content(): JSX.Element;
    disabled(): boolean;
    select(discussion: Discussion): void;
    remove(discussion: Discussion): void;
    changeType(key: string): void;
    changeOrdering(key: string): void;
    loadPreview(): Promise<boolean | void>;
    submit(e: Event): Promise<void>;
    getRequestData(method?: string): any;
}
