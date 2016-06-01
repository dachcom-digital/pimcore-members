pimcore.registerNS('pimcore.layout.toolbar');
pimcore.registerNS('pimcore.plugin.members');

pimcore.plugin.members = Class.create(pimcore.plugin.admin, {

    settings: {},
    ready : false,
    dataQueue: [],

    getClassName: function () {
        return 'pimcore.plugin.members';
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    uninstall: function () {
    },

    pimcoreReady: function (params, broker) {

        Ext.Ajax.request({
            url: '/plugin/Members/admin_restriction/get-global-settings',
            success: function (response) {
                var resp = Ext.decode(response.responseText);

                this.settings = resp.settings;
                this.ready = true;
                this.processQueue();

            }.bind(this)

        });

        var user = pimcore.globalmanager.get('user');

        if (user.isAllowed('plugins')) {

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

    openSettings: function () {
        try {
            pimcore.globalmanager.get('members_settings').activate();
        }
        catch (e) {
            pimcore.globalmanager.add('members_settings', new pimcore.plugin.members.settings());
        }
    },

    postOpenDocument: function (doc) {

        if( this.ready ) {
            this.processElement(doc, 'page');
        } else {
            this.addElementToQueue(doc, 'page');
        }

    },

    postOpenObject: function (obj) {

        if( this.ready ) {
            this.processElement(obj, 'object');
        } else {
            this.addElementToQueue(obj, 'object');
        }

    },

    postSaveDocument: function (doc, type, task, only) {

        doc.members.restrictionTab.save();

    },

    postSaveObject: function (obj, task, only) {

        obj.members.restrictionTab.save();

    },

    addElementToQueue: function(obj, type) {

        this.dataQueue.push({'obj' : obj, 'type' : type});
    },

    processQueue: function() {

        if(this.dataQueue.length > 0) {

            Ext.each(this.dataQueue, function(data) {

                var obj = data.obj,
                    type = data.type;

                this.processElement(obj, type);

            }.bind(this));

            this.dataQueue = {};
        }
    },

    processElement: function(obj, type) {

        obj.members = {};

        var isAllowed = true;

        if(type == 'object' && this.settings['core.settings.object.allowed'].indexOf(obj.data.general.o_className) === -1) {
            isAllowed = false;
        } else if(type == 'page' && obj.type !== 'page') {
            isAllowed = false;
        }

        if(isAllowed) {
            var restrictionTab = new pimcore.plugin.members.document.restriction(obj);
            restrictionTab.setup(type);
        }

    }

});

new pimcore.plugin.members();