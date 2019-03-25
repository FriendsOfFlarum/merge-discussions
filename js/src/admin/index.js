import PermissionGrid from 'flarum/components/PermissionGrid';
import { extend } from 'flarum/extend';

app.initializers.add('fof/merge-discussions', () => {
    extend(PermissionGrid.prototype, 'moderateItems', items => {
        items.add(
            'mergeDiscussions',
            {
                icon: 'fas fa-code-branch fa-flip-vertical',
                label: app.translator.trans('fof-merge-discussions.admin.permissions.merge_discussions_label'),
                permission: 'discussion.merge',
            },
            64
        );
    });
});
