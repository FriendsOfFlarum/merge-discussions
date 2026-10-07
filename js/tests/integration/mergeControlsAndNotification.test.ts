import { describe, it, expect, beforeAll } from '@jest/globals';
import bootstrapForum from '@flarum/jest-config/src/bootstrap/forum';
import app from 'flarum/forum/app';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import m from 'mithril';
import fs from 'fs';
import path from 'path';
import jsYaml from 'js-yaml';
import flatten from 'flat';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import DiscussionMergedNotification from '../../src/forum/components/DiscussionMergedNotification';
import * as forum from '../../src/forum';

/**
 * Where merging shows up in the forum: the discussion's moderation controls,
 * and the notification its author gets.
 */

beforeAll(() => {
  // @flarum/jest-config extends its own nested copy of dayjs; core's
  // components use this one, e.g. for the notification's time.
  dayjs.extend(relativeTime);

  bootstrapForum();
  // As the forum does for each enabled extension, so its extenders apply.
  app.bootExtensions({ 'fof-merge-discussions': forum });
  app.boot();

  const locale = path.resolve(process.cwd(), '../resources/locale/en.yml');
  app.translator.addTranslations(flatten(jsYaml.load(fs.readFileSync(locale, 'utf8'))) as Record<string, string>);
});

function discussion(id: string, canMerge: boolean) {
  app.store.pushPayload({ data: { type: 'discussions', id, attributes: { title: `Discussion ${id}`, slug: `discussion-${id}`, canMerge } } });

  return app.store.getById('discussions', id)!;
}

describe('the discussion controls', () => {
  it('offer merging to someone who can merge the discussion', () => {
    expect(DiscussionControls.moderationControls(discussion('1', true)).has('fof-merge')).toBe(true);
  });

  it('do not offer it otherwise', () => {
    expect(DiscussionControls.moderationControls(discussion('2', false)).has('fof-merge')).toBe(false);
  });
});

describe('the merged-discussion notification', () => {
  function render() {
    app.store.pushPayload({
      data: {
        type: 'notifications',
        id: '1',
        attributes: { contentType: 'discussionMerged', content: { merged_title: 'Old thread', merged_id: 9 }, createdAt: new Date().toISOString() },
        relationships: {
          subject: { data: { type: 'discussions', id: '5' } },
          fromUser: { data: { type: 'users', id: '3' } },
        },
      },
      included: [
        { type: 'discussions', id: '5', attributes: { title: 'Merged into', slug: 'merged-into' } },
        { type: 'users', id: '3', attributes: { username: 'moderator', displayName: 'moderator' } },
      ],
    });

    const root = document.createElement('div');
    m.render(root, m(DiscussionMergedNotification as any, { notification: app.store.getById('notifications', '1') }));

    return root;
  }

  it('says which discussion was merged, by whom', () => {
    expect(render().textContent).toContain('Your discussion Old thread was merged into this discussion by moderator.');
  });

  it('links to the discussion it was merged into', () => {
    // With or without the id, depending on the forum's slug driver.
    expect(render().querySelector('a')?.getAttribute('href')).toMatch(/\/d\/(5-)?merged-into$/);
  });
});
