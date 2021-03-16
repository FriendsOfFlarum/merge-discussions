import app from 'flarum/common/app';

app.initializers.add('fof/merge-discussions', () => {
    app.extensionData.for('fof-merge-discussions').registerPermission(
        {
            icon: 'fas fa-code-branch fa-flip-vertical',
            label: app.translator.trans('fof-merge-discussions.admin.permissions.merge_discussions_label'),
            permission: 'discussion.merge',
        },
        'moderate'
    );
});
