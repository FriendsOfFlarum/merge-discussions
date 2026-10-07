import { jest, describe, it, expect, beforeAll, afterEach } from '@jest/globals';
import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import app from 'flarum/forum/app';
import m from 'mithril';
import DiscussionMergeModal from '../../src/forum/components/DiscussionMergeModal';

/**
 * The merge modal as a moderator uses it: finding the discussions to merge,
 * and what it then asks the API to do.
 */

const THIS_ONE = { type: 'discussions', id: '1', attributes: { title: 'This one' } };
const CHOSEN = { type: 'discussions', id: '2', attributes: { title: 'Already chosen' } };
const ANOTHER = { type: 'discussions', id: '3', attributes: { title: 'Another' } };

beforeAll(() => {
  bootstrapForum();
  app.boot();

  app.store.pushPayload({ data: [THIS_ONE, CHOSEN, ANOTHER] });
});

let root: HTMLElement | null = null;

afterEach(() => {
  if (root) {
    m.mount(root, null);
    root.remove();
    root = null;
  }

  jest.restoreAllMocks();
});

/**
 * Open the modal on "This one", with "Already chosen" chosen to merge.
 */
function openModal(): HTMLElement {
  root = document.createElement('div');
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

  return root;
}

/**
 * Stand in for the API, answering every request with the given document.
 */
function api(response: object = {}) {
  return jest.spyOn(app, 'request').mockResolvedValue(response as never);
}

/**
 * Type into the modal's search box, and return the ids of the discussions it
 * lists once the search is done.
 */
async function search(modal: HTMLElement, query: string): Promise<string[]> {
  const input = modal.querySelector<HTMLInputElement>('.MergeDiscussions-Search input')!;

  input.focus();
  input.value = query;
  input.dispatchEvent(new Event('input'));

  // The search waits for typing to pause before it looks anything up.
  await new Promise((resolve) => setTimeout(resolve, 300));
  m.redraw.sync();

  return Array.from(modal.querySelectorAll<HTMLElement>('.DiscussionSearchResult[data-id]'), (result) => result.dataset.id!);
}

describe('the merge search', () => {
  it('writes nothing to the browser console', async () => {
    const log = jest.spyOn(console, 'log').mockImplementation(() => {});
    api({ data: [THIS_ONE, CHOSEN, ANOTHER] });

    await search(openModal(), 'one');

    expect(log).not.toHaveBeenCalled();
  });
});
