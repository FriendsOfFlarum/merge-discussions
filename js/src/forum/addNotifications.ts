import app from 'flarum/forum/app';
import DiscussionMergedNotification from './components/DiscussionMergedNotification';
import extendNotifications from './extendNotifications';

export default function (): void {
  app.notificationComponents.discussionMerged = DiscussionMergedNotification;
  extendNotifications();
}
