import EventPost from 'flarum/forum/components/EventPost';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';
import type { IPostAttrs } from 'flarum/forum/components/Post';

interface DiscussionMergePostAttrs extends IPostAttrs {
  mergeCount?: number;
  mergeTitles?: string;
}

export default class DiscussionMergePost extends EventPost {
  attrs!: DiscussionMergePostAttrs;

  static initAttrs(attrs: any) {
    super.initAttrs(attrs);

    const content = attrs.post.content();

    // Extract count and titles from the post content
    attrs.mergeCount = content?.count || 0;
    attrs.mergeTitles = Array.isArray(content?.titles) ? punctuateSeries(content.titles).join('') : '';
  }

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
    const data = {
      count: <strong>{this.attrs.mergeCount}</strong>,
      titles: <em>{this.attrs.mergeTitles}</em>,
    };

    return data;
  }
}
