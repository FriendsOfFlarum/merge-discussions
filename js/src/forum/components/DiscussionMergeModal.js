import app from 'flarum/forum/app';
import DiscussionPage from 'flarum/forum/components/DiscussionPage';
import IndexPage from 'flarum/forum/components/IndexPage';
import Button from 'flarum/common/components/Button';
import Modal from 'flarum/common/components/Modal';
import PostStream from 'flarum/forum/components/PostStream';
import PostStreamState from 'flarum/forum/states/PostStreamState';
import GlobalSearchState from 'flarum/forum/states/GlobalSearchState';
import Stream from 'flarum/common/utils/Stream';
import classList from 'flarum/common/utils/classList';

import DiscussionSearch from './DiscussionSearch';

export default class DiscussionMergeModal extends Modal {
    oninit(vnode) {
        super.oninit(vnode);

        this.discussion = this.attrs.discussion;
        this.type = Stream('target');
        this.merging = [];

        this.results = [];
        this.preview = null;

        this.loadingPreview = false;

        this.search = new GlobalSearchState();
    }

    title() {
        return app.translator.trans('fof-merge-discussions.forum.modal.title');
    }

    className() {
        return 'FoFMergeDiscussionsModal Modal--large';
    }

    content() {
        return (
            <div className="Modal-body">
                <div className="Form">
                    <div className="Form-group">
                        {['target', 'from'].map((key) => (
                            <div>
                                <input type="radio" id={`type_${key}`} checked={this.type() === key} onclick={this.changeType.bind(this, key)} />
                                &nbsp;
                                <label htmlFor={`type_${key}`}>{app.translator.trans(`fof-merge-discussions.forum.modal.type_${key}_label`)}</label>
                            </div>
                        ))}
                    </div>

                    <p className="help">
                        {app.translator.trans(`fof-merge-discussions.forum.modal.type_${this.type()}_help_text`, {
                            title: this.discussion.title(),
                        })}
                    </p>

                    <div className={classList('FormGroup', this.disabled() && 'hidden')}>
                        {DiscussionSearch.component({
                            state: this.search,
                            onSelect: this.select.bind(this),
                            ignore: this.discussion.id(),
                        })}
                    </div>

                    <div className="Form-group MergeDiscussions-Discussions">
                        <ul>
                            {this.merging.map((d) => (
                                <li>
                                    <i className="fas fa-trash DeleteEntry-Button" onclick={() => this.remove(d)} />
                                    &nbsp;
                                    <a href={`${app.forum.attribute('baseUrl')}/d/${d.id()}`} target="_blank">
                                        <i>{d.id()}</i> ~ {d.title()}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div className="Form-group MergeDiscussions-Preview">
                        {Button.component(
                            {
                                className: 'Button Button--danger',
                                onclick: this.loadPreview.bind(this),
                                loading: this.loadingPreview,
                                disabled: !this.discussion || !this.merging.length,
                            },
                            app.translator.trans('fof-merge-discussions.forum.modal.load_preview_button')
                        )}
                        {this.preview && (
                            <div className="MergeDiscussions-PostStream">
                                <div className="Hero">
                                    <h2>{this.type() === 'target' ? this.discussion.title() : this.merging[0].title()}</h2>
                                </div>
                                <PostStream stream={this.preview} discussion={this.preview.discussion} />
                            </div>
                        )}
                    </div>
                    <div className="Form-group">
                        {Button.component(
                            {
                                className: 'Button Button--primary Button--block',
                                type: 'submit',
                                onclick: this.submit.bind(this),
                                loading: this.loading,
                                disabled: !this.discussion || !this.merging.length,
                            },
                            app.translator.trans('fof-merge-discussions.forum.modal.submit_button')
                        )}
                    </div>
                </div>
            </div>
        );
    }

    disabled() {
        return this.type() === 'from' && this.merging.length !== 0;
    }

    select(discussion) {
        if (discussion && discussion.id() === this.discussion.id()) return;

        if (!this.merging.includes(discussion) && !this.disabled()) {
            this.merging.push(discussion);
            delete this.preview;
        }
    }

    remove(discussion) {
        delete this.preview;

        this.merging.splice(this.merging.indexOf(this.merging.filter((d) => d.id() === discussion.id())[0]), 1);
    }

    changeType(key) {
        this.type(key);

        if (this.merging.length > 1) this.merging = [];
    }

    loadPreview() {
        this.loadingPreview = true;

        return app
            .request(this.getRequestData('GET'))
            .then((payload) => {
                let number = 1;

                if (payload.included) payload.included.map(app.store.pushObject.bind(app.store));

                payload.data.relationships.posts.data
                    .map((record) => app.store.getById('posts', record.id))
                    .sort((a, b) => a.createdAt() - b.createdAt())
                    .forEach((p, i) => {
                        p.number(number++);

                        payload.data.relationships.posts.data[i] = {
                            type: 'posts',
                            id: p.id(),
                        };
                    });

                const discussion = app.store.createRecord(payload.data.type, payload.data);
                discussion.payload = payload;

                this.loadingPreview = false;
                const includedPosts = discussion.posts();

                this.preview = new PostStreamState(discussion, includedPosts);

                m.redraw();
            })
            .catch(() => (this.loadingPreview = false));
    }

    submit(e) {
        e.preventDefault();

        this.loading = true;

        return app
            .request(this.getRequestData())
            .then(async () => {
                const final = this.type() === 'target' ? this.discussion : this.merging[0];

                if (app.current.matches(DiscussionPage)) {
                    console.log('updating discussion page');
                    if (this.type() === 'target') {
                        await app.store.find('discussions', final.id());

                        await app.current.get('stream').update();
                    } else {
                        m.route.set(app.route.discussion(final));
                    }
                } else if (app.current.matches(IndexPage)) {
                    await app.store.find('discussions', final.id());
                }

                console.log('removing old discussions');

                if (this.type() === 'target') {
                    this.merging.forEach((d) => app.discussions.removeDiscussion(d));
                } else {
                    app.discussions.removeDiscussion(this.discussion);
                }

                app.modal.close();
            })
            .catch(console.error)
            .then(this.loaded.bind(this));
    }

    getRequestData(method = 'POST') {
        const isTarget = this.type() === 'target';
        const endpoint = isTarget ? this.discussion.apiEndpoint() : this.merging[0].apiEndpoint();
        const merging = isTarget ? this.merging.map((d) => d.id()) : this.discussion.id();

        return {
            method,
            url: `${app.forum.attribute('apiUrl')}${endpoint}/merge`,
            params: {
                ids: merging,
            },
            body: {
                ids: merging,
            },
            errorHandler: this.onerror.bind(this),
        };
    }
}
