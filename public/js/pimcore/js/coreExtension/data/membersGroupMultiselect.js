
pimcore.registerNS('pimcore.object.classes.data.membersGroupMultiselect');
pimcore.object.classes.data.membersGroupMultiselect = Class.create(pimcore.plugin.members.object.classes.data.dataMultiselect, {

    type: 'membersGroupMultiselect',

    getTypeName: function () {
        return t('members_user_group_multiselect');
    },

    getIconClass: function () {
        return 'members_icon_user_group';
    },

    getGroup: function () {
        return 'members';
    }

});