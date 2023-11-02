import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Button from 'flarum/common/components/Button';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import DiscussionMergeModal from './components/DiscussionMergeModal';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
import Discussion from 'flarum/common/models/Discussion';

export default function extendDiscussionControls() {
  extend(DiscussionControls, 'moderationControls', function (items: ItemList<Mithril.Children>, discussion: Discussion) {
    if (!discussion.canMerge()) return;

    items.add(
      'fof-merge',
      <Button icon="fas fa-code-branch fa-flip-vertical" onclick={() => app.modal.show(DiscussionMergeModal, { discussion })}>
        {app.translator.trans('fof-merge-discussions.forum.discussion.merge')}
      </Button>
    );
  });
}
