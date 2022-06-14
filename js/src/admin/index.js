import app from 'flarum/admin/app';

app.initializers.add('fof/merge-discussions', () => {
  app.extensionData
    .for('fof-merge-discussions')
    .registerPermission(
      {
        icon: 'fas fa-code-branch fa-flip-vertical',
        label: app.translator.trans('fof-merge-discussions.admin.permissions.merge_discussions_label'),
        permission: 'discussion.merge',
      },
      'moderate'
    )
    .registerSetting({
      label: app.translator.trans('fof-merge-discussions.admin.settings.search_result_label'),
      type: 'number',
      setting: 'fof-merge-discussions.search_limit',
      placeholder: '4',
      help: app.translator.trans('fof-merge-discussions.admin.settings.search_result_help'),
      min: 1,
      max: 99,
    });
});
