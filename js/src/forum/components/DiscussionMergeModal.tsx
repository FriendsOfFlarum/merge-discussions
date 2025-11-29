import Form from 'flarum/common/components/Form';
import app from 'flarum/forum/app';
import DiscussionPage from 'flarum/forum/components/DiscussionPage';
import IndexPage from 'flarum/forum/components/IndexPage';
import Button from 'flarum/common/components/Button';
import FormModal from 'flarum/common/components/FormModal';
import PostStreamState from 'flarum/forum/states/PostStreamState';
import SearchState from 'flarum/common/states/SearchState';
import Stream from 'flarum/common/utils/Stream';
import classList from 'flarum/common/utils/classList';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

import DiscussionSearch from './DiscussionSearch';
import ItemList from 'flarum/common/utils/ItemList';
import Icon from 'flarum/common/components/Icon';
import Tooltip from 'flarum/common/components/Tooltip';

export interface DiscussionMergeModalAttrs {
  discussion: Discussion;
  preselect?: Discussion;
}

export default class DiscussionMergeModal extends FormModal<any> {
  discussion!: Discussion;
  type!: Stream<string>;
  order!: Stream<string>;
  merging!: Discussion[];
  results!: any[];
  preview!: any;
  loadingPreview!: boolean;
  searchState!: SearchState;
  PostStream!: any;

  oninit(vnode: Mithril.Vnode<any, this>): void {
    super.oninit(vnode);

    this.discussion = this.attrs.discussion;

    this.type = Stream('target');
    this.order = Stream('date');
    this.merging = [];

    if (this.attrs.preselect) {
      this.merging.push(this.attrs.preselect);
    }

    this.results = [];
    this.preview = null;

    this.loadingPreview = false;

    this.searchState = new SearchState();

    // Lazy load PostStream component
    this.PostStream = null;
    import('flarum/forum/components/PostStream').then((module) => {
      this.PostStream = module.default;
      m.redraw();
    });
  }

  onready(): void {
    this.$('.Search-input .FormControl').focus();
  }

  title() {
    return app.translator.trans('fof-merge-discussions.forum.modal.title');
  }

  className() {
    return 'FoFMergeDiscussionsModal Modal--large';
  }

  types() {
    return ['target', 'from'];
  }

  ordering() {
    return ['date', 'suffix'];
  }

  typeItems() {
    const items = new ItemList();

    items.add('heading', <h3>{app.translator.trans('fof-merge-discussions.forum.modal.type_heading')}</h3>, 100);

    let priority = 90;

    this.types().map((key) => {
      items.add(
        `type_${key}`,
        <div>
          <input type="radio" id={`type_${key}`} checked={this.type() === key} onclick={this.changeType.bind(this, key)} />
          &nbsp;
          <label htmlFor={`type_${key}`}>{app.translator.trans(`fof-merge-discussions.forum.modal.type_${key}_label`)}</label>
        </div>,
        priority
      );

      priority -= 5;
    });

    return items;
  }

  orderItems() {
    const items = new ItemList();

    items.add('heading', <h3>{app.translator.trans('fof-merge-discussions.forum.modal.ordering_heading')}</h3>, 100);

    let priority = 90;

    this.ordering().map((key) => {
      items.add(
        `ordering_${key}`,
        <div>
          <input type="radio" id={`ordering_${key}`} checked={this.order() === key} onclick={this.changeOrdering.bind(this, key)} />
          &nbsp;
          <label htmlFor={`ordering_${key}`}>{app.translator.trans(`fof-merge-discussions.forum.modal.ordering_${key}_label`)}</label>
          &nbsp;
          <Tooltip text={app.translator.trans(`fof-merge-discussions.forum.modal.ordering_${key}_help`)}>
            <Icon name="fas fa-info-circle" />
          </Tooltip>
        </div>,
        priority
      );

      priority -= 5;
    });

    return items;
  }

  content() {
    return (
      <div className="Modal-body">
        <Form>
          <div className="Forum-group">{this.orderItems().toArray()}</div>
          <div className="Form-group">{this.typeItems().toArray()}</div>
          <p className="help">
            {app.translator.trans(`fof-merge-discussions.forum.modal.type_${this.type()}_help_text`, {
              title: this.discussion.title(),
            })}
          </p>
          <div className={classList('FormGroup', this.disabled() && 'hidden')}>
            <DiscussionSearch state={this.searchState} onSelect={this.select.bind(this)} ignore={this.discussion.id()} />
          </div>
          <div className="Form-group MergeDiscussions-Discussions">
            <ul>
              {this.merging.map((d) => (
                <li>
                  <i className="fas fa-trash DeleteEntry-Button" onclick={() => this.remove(d)} />
                  <a href={`${app.forum.attribute('baseUrl')}/d/${d.id()}`} target="_blank">
                    <i>{d.id()}</i>~ {d.title()}
                  </a>
                </li>
              ))}
            </ul>
          </div>
          <div className="Form-group MergeDiscussions-Preview">
            <Button
              className="Button Button--danger"
              onclick={this.loadPreview.bind(this)}
              loading={this.loadingPreview}
              disabled={!this.discussion || !this.merging.length}
            >
              {app.translator.trans('fof-merge-discussions.forum.modal.load_preview_button')}
            </Button>
            {this.preview && this.PostStream && (
              <div className="MergeDiscussions-PostStream">
                <div className="Hero">
                  <h2>{this.type() === 'target' ? this.discussion.title() : this.merging[0].title()}</h2>
                </div>
                <this.PostStream stream={this.preview} discussion={this.preview.discussion} onPositionChange={() => {}} />
              </div>
            )}
          </div>
          <div className="Form-group">
            <Button
              className="Button Button--primary Button--block"
              type="submit"
              onclick={this.submit.bind(this)}
              loading={this.loading}
              disabled={!this.discussion || !this.merging.length}
            >
              {app.translator.trans('fof-merge-discussions.forum.modal.submit_button')}
            </Button>
          </div>
        </Form>
      </div>
    );
  }

  disabled() {
    return this.type() === 'from' && this.merging.length !== 0;
  }

  select(discussion: Discussion): void {
    if (discussion && discussion.id() === this.discussion.id()) return;

    if (!this.merging.includes(discussion) && !this.disabled()) {
      this.merging.push(discussion);
      delete this.preview;
    }
  }

  remove(discussion: Discussion): void {
    delete this.preview;

    this.merging.splice(this.merging.indexOf(this.merging.filter((d) => d.id() === discussion.id())[0]), 1);
  }

  changeType(key: string): void {
    this.type(key);

    if (this.merging.length > 1) this.merging = [];
  }

  changeOrdering(key: string): void {
    this.order(key);
    if (this.preview) delete this.preview;
  }

  loadPreview() {
    this.loadingPreview = true;

    return app
      .request(this.getRequestData('GET'))
      .then((payload: any) => {
        let number = 1;

        if (payload.included) payload.included.map(app.store.pushObject.bind(app.store));

        let posts = payload.data.relationships.posts.data.map((record: any) => app.store.getById('posts', record.id));

        // apply date-sort only if ordering === 'date'
        if (this.order() === 'date') {
          posts.sort((a: any, b: any) => a.createdAt() - b.createdAt());
        }

        // then renumber & rebuild the relationship array
        posts.forEach((p: any, i: number) => {
          p.number(i + 1);
          payload.data.relationships.posts.data[i] = {
            type: 'posts',
            id: p.id(),
          };
        });

        const discussion = app.store.createRecord(payload.data.type, payload.data) as any;
        discussion.payload = payload;

        this.loadingPreview = false;
        const includedPosts = discussion.posts();

        this.preview = new PostStreamState(discussion as Discussion, includedPosts);

        // Set the visible range to show all posts (disable pagination in preview)
        this.preview.visibleStart = 0;
        this.preview.visibleEnd = posts.length;

        m.redraw();
      })
      .catch(() => (this.loadingPreview = false));
  }

  submit(e: Event) {
    e.preventDefault();

    this.loading = true;

    return app
      .request(this.getRequestData())
      .then(async () => {
        const final = this.type() === 'target' ? this.discussion : this.merging[0];

        if (app.current.matches(DiscussionPage)) {
          if (this.type() === 'target') {
            await app.store.find('discussions', final.id() || '');

            await app.current.get('stream').update();
          } else {
            m.route.set(app.route.discussion(final));
          }
        } else if (app.current.matches(IndexPage)) {
          await app.store.find('discussions', final.id() || '');
        }

        if (this.type() === 'target') {
          this.merging.forEach((d) => app.discussions.removeDiscussion(d));
        } else {
          app.discussions.removeDiscussion(this.discussion);
        }

        app.modal.close();
      })
      .catch((error) => {
        console.error('Merge error:', error);
        this.loading = false;
        m.redraw();
      })
      .then(this.loaded.bind(this));
  }

  getRequestData(method = 'POST'): any {
    const isTarget = this.type() === 'target';
    const endpoint = isTarget ? (this.discussion as any).apiEndpoint() : (this.merging[0] as any).apiEndpoint();
    const merging = isTarget ? this.merging.map((d) => d.id()) : this.discussion.id();
    const ordering = this.order();

    // Convert array to comma-separated string for GET requests
    const byIdsParam = method === 'GET' ? (Array.isArray(merging) ? merging.join(',') : String(merging)) : undefined;

    const requestData: any = {
      method,
      url: `${app.forum.attribute('apiUrl')}${endpoint}/merge${method === 'GET' ? '-preview' : ''}`,
      errorHandler: this.onerror.bind(this),
    };

    if (method === 'GET') {
      // For GET requests, use query parameters
      requestData.params = {
        byIds: byIdsParam,
        byOrdering: ordering,
      };
    } else {
      // For POST requests, use body
      requestData.body = {
        ids: merging,
        ordering,
      };
    }

    return requestData;
  }
}
