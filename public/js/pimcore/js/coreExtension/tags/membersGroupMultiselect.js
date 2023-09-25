
pimcore.registerNS('pimcore.object.tags.membersGroupMultiselect');
pimcore.object.tags.membersGroupMultiselect = Class.create(pimcore.plugin.members.object.tags.multiselect, {
    type: 'membersGroupMultiselect',
    storeName : 'userGroups'
});