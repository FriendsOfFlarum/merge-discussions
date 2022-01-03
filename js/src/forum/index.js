import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Model from 'flarum/common/Model';
import Button from 'flarum/common/components/Button';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';

import DiscussionMergeModal from './components/DiscussionMergeModal';
import DiscussionMergePost from './components/DiscussionMergePost';
import addNotifications from './addNotifications';

app.initializers.add('fof/merge-discussions', () => {
  app.store.models.discussions.prototype.canMerge = Model.attribute('canMerge');

  app.postComponents.discussionMerged = DiscussionMergePost;

  extend(DiscussionControls, 'moderationControls', function (items, discussion) {
    if (!discussion.canMerge()) return;

    items.add(
      'fof-merge',
      Button.component(
        {
          icon: 'fas fa-code-branch fa-flip-vertical',
          onclick: () => app.modal.show(DiscussionMergeModal, { discussion }),
        },
        app.translator.trans('fof-merge-discussions.forum.discussion.merge')
      )
    );
  });

  addNotifications();
});
