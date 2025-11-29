/// <reference types="mithril" />
import EventPost from 'flarum/forum/components/EventPost';
import type { IPostAttrs } from 'flarum/forum/components/Post';
interface DiscussionMergePostAttrs extends IPostAttrs {
    mergeCount?: number;
    mergeTitles?: string;
}
export default class DiscussionMergePost extends EventPost {
    attrs: DiscussionMergePostAttrs;
    static initAttrs(attrs: any): void;
    /**
     * Get the name of the event icon.
     */
    icon(): string;
    /**
     * Get the translation key for the description of the event.
     */
    descriptionKey(): string;
    /**
     * Get the translation data for the description of the event.
     */
    descriptionData(): {
        count: JSX.Element;
        titles: JSX.Element;
    };
}
export {};
