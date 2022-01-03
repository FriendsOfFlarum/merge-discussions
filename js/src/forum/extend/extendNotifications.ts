import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import ItemList from 'flarum/common/utils/ItemList';
import NotificationGrid from 'flarum/forum/components/NotificationGrid';

export default function () {
  extend(NotificationGrid.prototype, 'notificationTypes', function (items: ItemList) {
    items.add('discussionMerged', {
      name: 'discussionMerged',
      icon: 'fas fa-code-branch fa-flip-vertical',
      label: app.translator.trans('fof-merge-discussions.forum.notification.preferences.discussion_merged'),
    });
  });
}
