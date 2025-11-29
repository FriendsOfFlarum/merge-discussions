import app from 'flarum/forum/app';
import Notification from 'flarum/forum/components/Notification';
import type Discussion from 'flarum/common/models/Discussion';

export default class DiscussionMergedNotification extends Notification {
  icon() {
    return 'fas fa-code-branch fa-flip-vertical';
  }

  href() {
    const notification = this.attrs.notification;
    const discussion = notification.subject() as Discussion | null;

    if (!discussion) {
      return '#';
    }

    return app.route.discussion(discussion);
  }

  content() {
    const notification = this.attrs.notification;
    const user = notification.fromUser();
    const oldDiscussion = notification.content() as { merged_title?: string } | undefined;
    const oldTitle = oldDiscussion?.merged_title || '';

    return app.translator.trans('fof-merge-discussions.forum.notification.discussion_merged', {
      user,
      oldTitle,
    });
  }

  excerpt() {
    return null;
  }
}
