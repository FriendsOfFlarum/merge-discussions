import app from 'flarum/forum/app';
import Search from 'flarum/forum/components/Search';
import ItemList from 'flarum/common/utils/ItemList';
import extractText from 'flarum/common/utils/extractText';
import Icon from 'flarum/common/components/Icon';
import DiscussionSearchSource from './DiscussionSearchSource';

export default class DiscussionSearch extends Search {
  oncreate(vnode) {
    super.oncreate(vnode);

    this.navigator.onSelect(() => {
      this.attrs.onSelect(app.store.getById('discussions', this.getItem(this.index).attr('data-id')));
      m.redraw();
    });
  }

  view() {
    const vdom = super.view();

    // Inject the search icon into the Search-input div
    const searchInput = vdom.children.find((child) => child?.attrs?.className?.includes('Search-input'));
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
    vdom.attrs.className = `MergeDiscussions-Search ${vdom.attrs.className}`;

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

  findClearButton(vnode) {
    if (vnode?.tag === 'button' && vnode?.attrs?.className?.includes('Search-clear')) return vnode;
    if (vnode?.children) {
      for (const child of vnode.children) {
        const found = this.findClearButton(child);
        if (found) return found;
      }
    }
    return null;
  }

  findInput(vnode) {
    if (vnode?.tag === 'input') return vnode;
    if (vnode?.children) {
      for (const child of vnode.children) {
        const found = this.findInput(child);
        if (found) return found;
      }
    }
    return null;
  }

  updateMaxHeight() {
    // Since we wrapped the input in an additional div, we need to adjust the selector
    const resultsElementMargin = 14;
    const inputControl = this.element.querySelector('.Search-input .FormControl');

    if (!inputControl) return;

    const maxHeight = window.innerHeight - inputControl.getBoundingClientRect().bottom - resultsElementMargin;
    const resultsElement = this.element.querySelector('.Search-results');

    if (resultsElement) {
      resultsElement.style.setProperty('max-height', `${maxHeight}px`);
    }
  }

  sourceItems() {
    const items = new ItemList();

    items.add('discussions', new DiscussionSearchSource(this.attrs.onSelect, this.attrs.ignore));

    return items;
  }
}
