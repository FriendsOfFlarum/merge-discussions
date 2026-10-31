import { jest, describe, it, expect, beforeAll, afterEach } from '@jest/globals';
import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import app from 'flarum/forum/app';
import m from 'mithril';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import jsYaml from 'js-yaml';
import flatten from 'flat';
import DiscussionMergeModal from '../../src/forum/components/DiscussionMergeModal';

/**
 * The merge preview only shows a window of the merged discussion, as the
 * whole of a mega thread is too much to render. The modal has to say so.
 */

function previewResponse(shown: number, total: number, offset = 0) {
  const posts = Array.from({ length: shown }, (_, i) => ({
    type: 'posts',
    id: String(100 + i),
    attributes: {
      number: offset + i + 1,
      createdAt: new Date(Date.UTC(2024, 0, 1, 0, i)).toISOString(),
      contentType: 'comment',
      contentHtml: `<p>Post ${offset + i + 1}</p>`,
    },
  }));

  return {
    data: {
      type: 'discussions',
      id: '1',
      attributes: { title: 'Target' },
      relationships: { posts: { data: posts.map(({ type, id }) => ({ type, id })) } },
    },
    included: posts,
    meta: { 'fof-merge-discussions': { totalPosts: total, offset } },
  };
}

/**
 * Wait, a macrotask at a time, until the modal shows what the test waits for:
 * the stubbed request and the lazily imported PostStream both settle later.
 */
async function waitFor(condition: () => boolean): Promise<void> {
  for (let tick = 0; tick < 50 && !condition(); tick++) {
    await new Promise((resolve) => setTimeout(resolve, 0));
    m.redraw.sync();
  }
}

/**
 * Mount the modal for real, click Preview, and return its text, whether the
 * preview rendered, and the request it sent, once the (stubbed) preview
 * response is in.
 */
async function preview(response: object): Promise<{ text: string; loaded: boolean; request: any }> {
  const request = jest.spyOn(app, 'request').mockResolvedValue(response as never);

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
  await waitFor(() => root.querySelector('.MergeDiscussions-PostStream') !== null);

  const text = root.textContent ?? '';
  const loaded = root.querySelector('.MergeDiscussions-PostStream') !== null;
  m.mount(root, null);
  root.remove();

  return { text, loaded, request: request.mock.calls[0][0] };
}

beforeAll(() => {
  // jsdom has no ResizeObserver, which core's post stream needs when it is
  // created; without one, the preview's post stream is only partly set up.
  globalThis.ResizeObserver ??= class {
    observe() {}
    unobserve() {}
    disconnect() {}
  } as unknown as typeof ResizeObserver;

  bootstrapForum();
  app.boot();

  const locale = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../../resources/locale/en.yml');
  app.translator.addTranslations(flatten(jsYaml.load(fs.readFileSync(locale, 'utf8'))) as Record<string, string>);
});

afterEach(() => {
  jest.restoreAllMocks();
});

describe('the merge preview', () => {
  it('says which posts of the merged discussion it shows, when it is cut short', async () => {
    expect((await preview(previewResponse(2, 20000))).text).toContain('Showing posts 1 to 2 of 20,000.');
  });

  it('says where the window starts, when it starts further in', async () => {
    expect((await preview(previewResponse(2, 20000, 9))).text).toContain('Showing posts 10 to 11 of 20,000.');
  });

  it('says nothing more when the whole merged discussion fits', async () => {
    const { text, loaded } = await preview(previewResponse(2, 2));

    expect(loaded).toBe(true);
    expect(text).not.toContain('Showing posts');
  });

  it('asks for this discussion merged with the chosen ones', async () => {
    const { request } = await preview(previewResponse(2, 2));

    expect(request.url).toMatch(/\/discussions\/1\/merge-preview$/);
    expect(request.params).toEqual({ byIds: '2', byOrdering: 'date' });
  });
});
