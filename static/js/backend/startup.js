pimcore.registerNS('pimcore.layout.toolbar');
pimcore.registerNS('pimcore.plugin.members');

pimcore.plugin.members = Class.create(pimcore.plugin.admin,{

    isInitialized : false,

    getClassName: function (){
        return 'pimcore.plugin.members';
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },

    uninstall: function(){
    },

    pimcoreReady: function (params, broker) {

        var user = pimcore.globalmanager.get('user');

        if(user.isAllowed('plugins')) {

            /*
            var membersMenu = new Ext.Action({
                id: 'members',
                text: t('members'),
                iconCls: 'members_icon',
                handler:this.openSettings
            });

            layoutToolbar.settingsMenu.add(membersMenu);

            */

        }

    },

    openSettings : function()
    {
        try {
            pimcore.globalmanager.get('members_settings').activate();
        }
        catch (e) {
            pimcore.globalmanager.add('members_settings', new pimcore.plugin.members.settings());
        }
    },

    postOpenDocument : function(doc) {

        doc.members = {};

        var restrictionTab = new pimcore.plugin.members.document.restriction(doc);

        restrictionTab.setup();

    },

    postSaveDocument : function(doc, type, task, only) {

        doc.members.restrictionTab.save();

    }

});

new pimcore.plugin.members();