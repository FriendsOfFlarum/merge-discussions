import Extend from 'flarum/common/extenders';
import Discussion from 'flarum/common/models/Discussion';
import DiscussionMergePost from './components/DiscussionMergePost';

export default [
  new Extend.PostTypes() //
    .add('discussionMerged', DiscussionMergePost),

  new Extend.Model(Discussion) //
    .attribute<boolean>('canMerge'),
];
