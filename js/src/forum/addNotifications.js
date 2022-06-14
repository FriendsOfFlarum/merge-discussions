import app from 'flarum/forum/app';
import DiscussionMergedNotification from './components/DiscussionMergedNotification';
import extendNotifications from './extend/extendNotifications';

export default function () {
  app.notificationComponents.discussionMerged = DiscussionMergedNotification;
  extendNotifications();
}
