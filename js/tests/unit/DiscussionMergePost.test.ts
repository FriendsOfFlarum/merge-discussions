import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import DiscussionMergePost from '../../src/forum/components/DiscussionMergePost';

/**
 * The merge notice post stores `{ count, titles }` as its content. These pin
 * how that content becomes the attrs the post renders.
 */
function initAttrs(content: unknown) {
  const attrs: any = { post: { content: () => content } };

  DiscussionMergePost.initAttrs(attrs);

  return attrs;
}

beforeAll(() => bootstrapForum());

describe('DiscussionMergePost.initAttrs', () => {
  it('reads the merged post count', () => {
    expect(initAttrs({ count: 3, titles: ['A'] }).mergeCount).toBe(3);
  });

  it('joins a single merged title as-is', () => {
    expect(initAttrs({ count: 1, titles: ['Old thread'] }).mergeTitles).toBe('Old thread');
  });

  it('falls back to zero and no titles when the content is missing', () => {
    const attrs = initAttrs(null);

    expect(attrs.mergeCount).toBe(0);
    expect(attrs.mergeTitles).toBe('');
  });
});
