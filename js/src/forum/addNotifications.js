import app from 'flarum/forum/app';
import DiscussionMergedNotification from './components/DiscussionMergedNotification';
import extendNotifications from './extendNotifications';

export default function () {
  app.notificationComponents.discussionMerged = DiscussionMergedNotification;
  extendNotifications();
}
