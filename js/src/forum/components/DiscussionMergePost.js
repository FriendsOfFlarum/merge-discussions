import EventPost from 'flarum/components/EventPost';

export default class DiscussionMergePost extends EventPost {
    /**
     * Get the name of the event icon.
     *
     * @return {String}
     */
    icon() {
        return 'fas fa-code-branch fa-flip-vertical';
    }

    /**
     * Get the translation key for the description of the event.
     *
     * @return {String}
     */
    descriptionKey() {
        return 'fof-merge-discussions.forum.post.merged';
    }

    /**
     * Get the translation data for the description of the event.
     *
     * @return {Object}
     */
    descriptionData() {
        return this.props.post.content();
    }
}
