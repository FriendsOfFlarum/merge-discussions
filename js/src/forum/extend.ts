import Extend from 'flarum/common/extenders';
import Discussion from 'flarum/common/models/Discussion';
import DiscussionMergePost from './components/DiscussionMergePost';
import DiscussionMergedNotification from './components/DiscussionMergedNotification';

export default [
  new Extend.PostTypes() //
    .add('discussionMerged', DiscussionMergePost),

  new Extend.Model(Discussion) //
    .attribute<boolean>('canMerge'),

  new Extend.Notification() //
    .add('discussionMerged', DiscussionMergedNotification),
];
