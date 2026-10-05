import app from 'flarum/forum/app';
import extendDiscussionControls from './extendDiscussionControls';
import extendNotifications from './extendNotifications';
import followMergeRedirects from './followMergeRedirects';

export { default as extend } from './extend';

app.initializers.add('fof/merge-discussions', () => {
  extendDiscussionControls();
  extendNotifications();
  followMergeRedirects();
});
