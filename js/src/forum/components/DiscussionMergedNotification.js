import app from 'flarum/forum/app';
import Notification from 'flarum/forum/components/Notification';

export default class DiscussionMergedNotification extends Notification {
  icon() {
    return 'fas fa-code-branch fa-flip-vertical';
  }

  href() {
    const notification = this.attrs.notification;
    const discussion = notification.subject();

    return app.route.discussion(discussion);
  }

  content() {
    const notification = this.attrs.notification;
    const user = notification.fromUser();
    const oldTitle = notification.content();

    return app.translator.trans('fof-merge-discussions.forum.notification.discussion_merged', {
      user,
      oldTitle
    });
  }
}
