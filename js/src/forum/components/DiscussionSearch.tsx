import app from 'flarum/forum/app';
import Search from 'flarum/forum/components/Search';
import ItemList from 'flarum/common/utils/ItemList';
import extractText from 'flarum/common/utils/extractText';
import Icon from 'flarum/common/components/Icon';
import DiscussionSearchSource from './DiscussionSearchSource';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

export interface DiscussionSearchAttrs extends Mithril.Attributes {
  state: any;
  onSelect: (discussion: Discussion) => void;
  ignore?: (discussion: Discussion) => boolean;
}

export default class DiscussionSearch extends Search<DiscussionSearchAttrs> {
  protected static MIN_SEARCH_LEN = 1;

  oncreate(vnode: Mithril.VnodeDOM<DiscussionSearchAttrs, this>): void {
    super.oncreate(vnode);

    this.navigator.onSelect(() => {
      const item = this.getItem(this.index);
      const dataId = item?.attr('data-id');
      if (dataId) {
        const discussion = app.store.getById<Discussion>('discussions', dataId);
        if (discussion) {
          this.attrs.onSelect(discussion);
        }
      }
      m.redraw();
    });
  }

  view(): Mithril.Vnode {
    const vdom = super.view() as Mithril.Vnode;

    // Inject the search icon into the Search-input div
    const children = Array.isArray(vdom.children) ? vdom.children : [];
    const searchInput = children.find((child: any) => child?.attrs?.className?.includes('Search-input')) as any;
    if (searchInput) {
      // Wrap the existing input in an Input container
      const originalInput = searchInput.children[0];
      searchInput.children = [
        <div className="Input Input--withPrefix Input--withClear">
          <Icon name="fas fa-search Input-prefix-icon" />
          {originalInput}
          {searchInput.children.slice(1)}
        </div>,
      ];
    }

    // Add custom class and use custom placeholder
    if (!vdom.attrs) (vdom as any).attrs = {};
    (vdom.attrs as any).className = `MergeDiscussions-Search ${(vdom.attrs as any).className || ''}`;

    // Update placeholder text in the input element
    const input = this.findInput(vdom);
    if (input) {
      const customLabel = extractText(app.translator.trans('fof-merge-discussions.forum.modal.search_discussions_placeholder'));
      input.attrs['aria-label'] = customLabel;
      input.attrs.placeholder = customLabel;
    }

    // Update clear button to use the correct translation key
    const clearButton = this.findClearButton(vdom);
    if (clearButton) {
      clearButton.attrs['aria-label'] = app.translator.trans('core.lib.search.search_clear_button_accessible_label');
    }

    return vdom;
  }

  findClearButton(vnode: any): any {
    if (vnode?.tag === 'button' && vnode?.attrs?.className?.includes('Search-clear')) return vnode;
    if (vnode?.children) {
      for (const child of vnode.children) {
        const found = this.findClearButton(child);
        if (found) return found;
      }
    }
    return null;
  }

  findInput(vnode: any): any {
    if (vnode?.tag === 'input') return vnode;
    if (vnode?.children) {
      for (const child of vnode.children) {
        const found = this.findInput(child);
        if (found) return found;
      }
    }
    return null;
  }

  updateMaxHeight(): void {
    // Since we wrapped the input in an additional div, we need to adjust the selector
    const resultsElementMargin = 14;
    const inputControl = this.element.querySelector('.Search-input .FormControl') as HTMLElement | null;

    if (!inputControl) return;

    const maxHeight = window.innerHeight - inputControl.getBoundingClientRect().bottom - resultsElementMargin;
    const resultsElement = this.element.querySelector('.Search-results') as HTMLElement | null;

    if (resultsElement) {
      resultsElement.style.setProperty('max-height', `${maxHeight}px`);
    }
  }

  sourceItems(): ItemList<any> {
    const items = new ItemList<any>();

    items.add('discussions', new DiscussionSearchSource(this.attrs.onSelect, this.attrs.ignore));

    return items;
  }
}
