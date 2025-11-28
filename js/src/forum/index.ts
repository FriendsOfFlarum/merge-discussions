import app from 'flarum/forum/app';
import addNotifications from './addNotifications';
import extendDiscussionControls from './extendDiscussionControls';

export { default as extend } from './extend';

app.initializers.add('fof/merge-discussions', () => {
  extendDiscussionControls();
  addNotifications();
});
