import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import ItemList from 'flarum/common/utils/ItemList';
import NotificationGrid from 'flarum/forum/components/NotificationGrid';
import type Mithril from 'mithril';

export default function () {
  extend(NotificationGrid.prototype, 'notificationTypes', function (items: ItemList<{ name: string; icon: string; label: Mithril.Children }>) {
    items.add('discussionMerged', {
      name: 'discussionMerged',
      icon: 'fas fa-code-branch fa-flip-vertical',
      label: app.translator.trans('fof-merge-discussions.forum.notification.preferences.discussion_merged'),
    });
  });
}
