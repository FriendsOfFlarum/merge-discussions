import { jest, describe, it, expect, beforeAll, afterEach } from '@jest/globals';
import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import app from 'flarum/forum/app';
import m from 'mithril';
import fs from 'fs';
import path from 'path';
import jsYaml from 'js-yaml';
import flatten from 'flat';
import DiscussionMergeModal from '../../src/forum/components/DiscussionMergeModal';

/**
 * The merge preview only shows the start of the merged discussion, as the
 * whole of a mega thread is too much to render. The modal has to say so.
 */

function previewResponse(shown: number, total: number) {
  const posts = Array.from({ length: shown }, (_, i) => ({
    type: 'posts',
    id: String(100 + i),
    attributes: { number: i + 1, createdAt: new Date(Date.UTC(2024, 0, 1, 0, i)).toISOString(), contentType: 'comment', contentHtml: `<p>Post ${i + 1}</p>` },
  }));

  return {
    data: {
      type: 'discussions',
      id: '1',
      attributes: { title: 'Target' },
      relationships: { posts: { data: posts.map(({ type, id }) => ({ type, id })) } },
    },
    included: posts,
    meta: { 'fof-merge-discussions': { totalPosts: total } },
  };
}

/**
 * Mount the modal for real, click Preview, and return its text, and whether
 * the preview rendered, once the (stubbed) preview response is in.
 */
async function previewText(response: object): Promise<{ text: string; loaded: boolean }> {
  jest.spyOn(app, 'request').mockResolvedValue(response as never);

  app.store.pushPayload({
    data: [
      { type: 'discussions', id: '1', attributes: { title: 'Target' } },
      { type: 'discussions', id: '2', attributes: { title: 'Source' } },
    ],
  });

  const root = document.createElement('div');
  document.body.appendChild(root);

  m.mount(root, {
    view: () =>
      m(DiscussionMergeModal as any, {
        discussion: app.store.getById('discussions', '1'),
        preselect: app.store.getById('discussions', '2'),
        animateShow: jest.fn(),
        animateHide: jest.fn(),
      }),
  });

  (root.querySelector('.MergeDiscussions-Preview .Button') as HTMLButtonElement).click();
  await new Promise((resolve) => setTimeout(resolve, 0));
  m.redraw.sync();

  const text = root.textContent ?? '';
  const loaded = root.querySelector('.MergeDiscussions-PostStream') !== null;
  m.mount(root, null);
  root.remove();

  return { text, loaded };
}

beforeAll(() => {
  bootstrapForum();
  app.boot();

  const locale = path.resolve(process.cwd(), '../resources/locale/en.yml');
  app.translator.addTranslations(flatten(jsYaml.load(fs.readFileSync(locale, 'utf8'))) as Record<string, string>);
});

afterEach(() => {
  jest.restoreAllMocks();
});

describe('the merge preview', () => {
  it('says how much of the merged discussion it shows, when it is cut short', async () => {
    expect((await previewText(previewResponse(2, 20000))).text).toContain('Showing the first 2 of 20,000 posts.');
  });

  it('says nothing more when the whole merged discussion fits', async () => {
    const { text, loaded } = await previewText(previewResponse(2, 2));

    expect(loaded).toBe(true);
    expect(text).not.toContain('Showing the first');
  });
});
