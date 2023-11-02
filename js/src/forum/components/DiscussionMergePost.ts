import EventPost from 'flarum/forum/components/EventPost';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';

export default class DiscussionMergePost extends EventPost {
  /**
   * Get the name of the event icon.
   */
  icon(): string {
    return 'fas fa-code-branch fa-flip-vertical';
  }

  /**
   * Get the translation key for the description of the event.
   */
  descriptionKey(): string {
    return 'fof-merge-discussions.forum.post.merged';
  }

  /**
   * Get the translation data for the description of the event.
   */
  descriptionData() {
    const data = this.attrs.post.content();

    if (Array.isArray(data?.titles)) data.titles = punctuateSeries(data.titles).join('');

    return data;
  }
}
