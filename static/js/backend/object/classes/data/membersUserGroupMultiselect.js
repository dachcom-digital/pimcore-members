pimcore.registerNS("pimcore.object.classes.data.membersUserGroupMultiselect");
pimcore.object.classes.data.membersUserGroupMultiselect = Class.create(pimcore.plugin.members.object.classes.data.dataMultiselect, {

    type: "membersUserGroupMultiselect",

    getTypeName: function () {
        return t("members_user_group_multiselect");
    },

    getIconClass: function () {
        return "members_icon_user_group";
    },

    getGroup: function () {
        return "member";
    }
});
