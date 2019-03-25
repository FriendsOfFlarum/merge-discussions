import { extend } from 'flarum/extend';
import Model from 'flarum/Model';
import DiscussionControls from 'flarum/utils/DiscussionControls';
import Button from 'flarum/components/Button';

import DiscussionMergeModal from './components/DiscussionMergeModal';
import DiscussionMergePost from './components/DiscussionMergePost';

app.initializers.add('fof/merge-discussions', () => {
    app.store.models.discussions.prototype.canMerge = Model.attribute('canMerge');

    app.postComponents.discussionMerged = DiscussionMergePost;

    extend(DiscussionControls, 'moderationControls', function(items, discussion) {
        if (!discussion.canMerge()) return;

        items.add(
            'fof-merge',
            Button.component({
                icon: 'fas fa-code-branch fa-flip-vertical',
                children: app.translator.trans('fof-merge-discussions.forum.discussion.merge'),
                onclick: () => app.modal.show(new DiscussionMergeModal(discussion)),
            })
        );
    });
});
