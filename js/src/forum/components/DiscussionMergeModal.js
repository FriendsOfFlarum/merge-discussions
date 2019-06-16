import DiscussionPage from 'flarum/components/DiscussionPage';
import Button from 'flarum/components/Button';
import Modal from 'flarum/components/Modal';
import PostStream from 'flarum/components/PostStream';

import DiscussionSearch from './DiscussionSearch';

export default class DiscussionMergeModal extends Modal {
    constructor(discussion) {
        super();

        this.discussion = discussion;
        this.merging = [];
    }

    init() {
        super.init();

        this.results = [];
        this.preview = null;

        this.loadingPreview = false;
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
                    <p className="help">
                        {app.translator.trans('fof-merge-discussions.forum.modal.help_text', {
                            title: this.discussion.title(),
                        })}
                    </p>

                    <div className="Form-group">
                        {DiscussionSearch.component({
                            onSelect: this.select.bind(this),
                            ignore: this.discussion.id(),
                        })}
                    </div>
                    <div className="Form-group MergeDiscussions-Discussions">
                        <ul>
                            {this.merging.map(d => (
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
                        {Button.component({
                            className: 'Button Button--danger',
                            onclick: this.loadPreview.bind(this),
                            loading: this.loadingPreview,
                            disabled: !this.discussion || !this.merging.length,
                            children: app.translator.trans('fof-merge-discussions.forum.modal.load_preview_button'),
                        })}
                        {this.preview && <div className="MergeDiscussions-PostStream">{this.preview.render()}</div>}
                    </div>
                    <div className="Form-group">
                        {Button.component({
                            className: 'Button Button--primary Button--block',
                            type: 'submit',
                            onclick: this.submit.bind(this),
                            loading: this.loading,
                            disabled: !this.discussion || !this.merging.length,
                            children: app.translator.trans('fof-merge-discussions.forum.modal.submit_button'),
                        })}
                    </div>
                </div>
            </div>
        );
    }

    select(discussion) {
        if (discussion && discussion.id() === this.discussion.id()) return;

        if (!this.merging.includes(discussion)) this.merging.push(discussion);
    }

    remove(discussion) {
        this.merging.splice(this.merging.indexOf(this.merging.filter(d => d.id() === discussion.id())[0]), 1);
    }

    loadPreview() {
        this.loadingPreview = true;

        return app
            .request(this.getRequestData('GET'))
            .then(payload => {
                let number = 1;

                if (payload.included) payload.included.map(app.store.pushObject.bind(app.store));

                payload.data.relationships.posts.data
                    .map(record => app.store.getById('posts', record.id))
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

                this.preview = new PostStream({ discussion, includedPosts });

                m.lazyRedraw();
            })
            .catch(() => (this.loadingPreview = false));
    }

    submit(e) {
        e.preventDefault();

        this.loading = true;

        return app
            .request(this.getRequestData())
            .then(async () => {
                if (app.current instanceof DiscussionPage) {
                    await app.current.refresh();
                    app.current.stream.update();
                }

                if (app.cache.discussionList) this.merging.forEach(d => app.cache.discussionList.removeDiscussion(d));

                m.redraw();

                app.modal.close();
            })
            .catch(() => (this.loading = false));
    }

    getRequestData(method = 'POST') {
        return {
            method,
            url: `${app.forum.attribute('apiUrl')}${this.discussion.apiEndpoint()}/merge`,
            data: {
                ids: this.merging.map(d => d.id()),
            },
            errorHandler: this.onerror.bind(this),
        };
    }
}
