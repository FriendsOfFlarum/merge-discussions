import Search from 'flarum/components/Search';
import ItemList from 'flarum/utils/ItemList';
import DiscussionSearchSource from './DiscussionSearchSource';

export default class DiscussionSearch extends Search {
    view() {
        this.hasFocus = true;

        const vdom = super.view();

        vdom.attrs.className = `MergeDiscussions-Search ${this.value() && 'open'} ` + vdom.attrs.className.replace(/(focused|open)/g, '');

        return vdom;
    }

    sourceItems() {
        const items = new ItemList();

        items.add('discussions', new DiscussionSearchSource(this.props.onSelect, this.props.ignore));

        return items;
    }
}
