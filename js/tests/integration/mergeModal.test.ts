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

/**
 * Submit the modal, and return the merge request it sends.
 */
async function submit(modal: HTMLElement, request: ReturnType<typeof api>): Promise<any> {
  modal.querySelector<HTMLButtonElement>('button[type="submit"]')!.click();
  await new Promise((resolve) => setTimeout(resolve, 0));

  return request.mock.calls.map(([options]) => options as any).find((options) => options.method === 'POST');
}

describe('the merge search', () => {
  it('finds a discussion by its number', async () => {
    const request = api({ data: ANOTHER });

    expect(await search(openModal(), '3')).toEqual(['3']);
    expect(request.mock.calls[0][0]).toMatchObject({ method: 'GET', url: expect.stringMatching(/\/discussions\/3$/) });
  });

  it('searches by text, listing as many discussions as the admin set', async () => {
    app.forum.pushAttributes({ 'fof-merge-discussions.search_limit': 7 });
    const request = api({ data: [ANOTHER] });

    expect(await search(openModal(), 'another')).toEqual(['3']);
    expect((request.mock.calls[0][0] as any).params).toEqual({ filter: { q: 'another' }, page: { limit: 7 } });
  });

  it('waits for three letters before searching by text', async () => {
    const request = api();

    await search(openModal(), 'an');

    expect(request).not.toHaveBeenCalled();
  });

  it('does not offer this discussion, or the ones already chosen', async () => {
    api({ data: [THIS_ONE, CHOSEN, ANOTHER] });

    expect(await search(openModal(), 'one')).toEqual(['3']);
  });

  it('adds a discussion picked from the results to the merge', async () => {
    const request = api({ data: [ANOTHER] });
    const modal = openModal();

    await search(modal, 'another');
    modal.querySelector<HTMLButtonElement>('.DiscussionSearchResult[data-id="3"] button')!.click();

    expect((await submit(modal, request)).body.ids).toEqual(['2', '3']);
  });

  it('writes nothing to the browser console', async () => {
    const log = jest.spyOn(console, 'log').mockImplementation(() => {});
    api({ data: [THIS_ONE, CHOSEN, ANOTHER] });

    await search(openModal(), 'one');

    expect(log).not.toHaveBeenCalled();
  });
});

describe('merging into this discussion', () => {
  it('posts the chosen discussions, and the chosen ordering, to this one', async () => {
    const request = api();
    const modal = openModal();

    modal.querySelector<HTMLInputElement>('#ordering_suffix')!.click();

    const merge = await submit(modal, request);

    expect(merge.url).toMatch(/\/discussions\/1\/merge$/);
    expect(merge.body).toEqual({ ids: ['2'], ordering: 'suffix' });
  });
});

describe('merging this discussion into another', () => {
  it('posts this one to the other, in date order unless chosen otherwise', async () => {
    const request = api();
    const modal = openModal();

    modal.querySelector<HTMLInputElement>('#type_from')!.click();

    const merge = await submit(modal, request);

    expect(merge.url).toMatch(/\/discussions\/2\/merge$/);
    expect(merge.body).toEqual({ ids: '1', ordering: 'date' });
  });
});
