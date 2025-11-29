import app from 'flarum/admin/app';
import Extend from 'flarum/common/extenders';

export default [
  new Extend.Admin() //
    .setting(() => ({
      label: app.translator.trans('fof-merge-discussions.admin.settings.search_result_label'),
      type: 'number',
      setting: 'fof-merge-discussions.search_limit',
      help: app.translator.trans('fof-merge-discussions.admin.settings.search_result_help'),
      min: 1,
      max: 99,
    }))
    .permission(
      () => ({
        icon: 'fas fa-code-branch fa-flip-vertical',
        label: app.translator.trans('fof-merge-discussions.admin.permissions.merge_discussions_label'),
        permission: 'discussion.merge',
      }),
      'moderate',
      75
    ),
];
