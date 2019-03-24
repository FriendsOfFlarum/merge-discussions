import Button from 'flarum/components/Button';
import Modal from 'flarum/components/Modal';

import DiscussionSearch from "./DiscussionSearch";

export default class DiscussionMergeModal extends Modal {
    constructor(discussion) {
        super();

        this.discussion = discussion;
        this.merging = [];
    }

    init() {
        super.init();

        this.query = m.prop('');
        this.results = [];
    }

    title() {
        return app.translator.trans('fof-merge-discussions.forum.modal.title');
    }

    content() {
        return (
            <div className="Modal-body">
                <div className="Form Form--centered">
                    <div className="Form-group">
                        {DiscussionSearch.component({
                            onSelect: this.select.bind(this),
                            ignore: this.discussion.id(),
                            selected: this.merging.map(d => d.id())
                        })}
                    </div>
                    <div className="Form-group">
                        <p style="text-align: left;">
                            <ul>
                                {this.merging.map(d =>
                                    <li>{d.title()}</li>
                                )}
                            </ul>
                        </p>
                    </div>
                    <div className="Form-group">
                        {Button.component({
                            className: 'Button Button--primary Button--block',
                            type: 'submit',
                            onclick: this.submit.bind(this),
                            loading: this.loading,
                            disabled: !this.discussion || !this.merging.length,
                            children: app.translator.trans('fof-merge-discussions.forum.modal.submit_button')
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

    submit(e) {
        e.preventDefault();

        this.loading = true;

        return app.request({
            url: `${app.forum.attribute('apiUrl')}/discussions/${this.discussion.id()}/merge`,
            method: 'POST',
            data: {
                ids: this.merging.map(d => d.id())
            },
            errorHandler: this.onerror.bind(this)
        }).then(res => {
            console.log(res);
        })
    }
}
