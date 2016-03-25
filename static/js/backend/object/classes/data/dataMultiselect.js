pimcore.registerNS("pimcore.plugin.members.object.classes.data.dataMultiselect");
pimcore.plugin.members.object.classes.data.dataMultiselect = Class.create(pimcore.object.classes.data.multiselect, {

    allowIn: {
        object: true,
        objectbrick: true,
        fieldcollection: true,
        localizedfield: true
    },

    initialize: function (treeNode, initData) {
        this.initData(initData);

        this.treeNode = treeNode;
    },

    getLayout: function ($super) {
        $super();

        this.specificPanel.removeAll();

        return this.layout;
    }
});
