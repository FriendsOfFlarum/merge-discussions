import Button from 'flarum/components/Button';
import Modal from 'flarum/components/Modal';
import DiscussionSearch from "./DiscussionSearch";

export default class DiscussionMergeModal extends Modal {
    constructor(discussion) {
        super();

        this.first = discussion;
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
                            ignore: this.first.id(),
                        })}
                    </div>
                    <div className="Form-group">
                        <p>
                            Merge <b>{this.first.title()}</b> into <b>{this.second && this.second.title() || '??'}</b>
                        </p>
                        {Button.component({
                            className: 'Button Button--primary Button--block',
                            type: 'submit',
                            onclick: this.submit.bind(this),
                            loading: this.loading,
                            disabled: !this.first || !this.second,
                            children: app.translator.trans('fof-merge-discussions.forum.modal.submit_button')
                        })}
                    </div>
                </div>
            </div>
        );
    }

    select(discussion) {
        if (discussion && discussion.id() === this.first.id()) return;

        this.second = discussion;
    }

    submit(e) {
        e.preventDefault();

        this.loading = true;

        return app.request({
            url: `${app.forum.attribute('apiUrl')}/discussions/merge`,
            method: 'POST',
            data: {
                ids: [this.first.id(), this.second.id()]
            },
            errorHandler: this.onerror.bind(this)
        }).then(res => {
            console.log(res);
        })
    }
}
