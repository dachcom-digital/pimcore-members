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
            url: '/admin/members/restriction/get-global-settings',
            success: function (response) {
                var resp = Ext.decode(response.responseText);

                this.settings = resp.settings;
                this.ready = true;
                this.processQueue();

            }.bind(this)

        });

        var user = pimcore.globalmanager.get('user');

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

    postOpenAsset: function (asset) {

        if( this.ready ) {
            this.processElement(asset, 'asset');
        } else {
            this.addElementToQueue(asset, 'asset');
        }

    },

    postSaveDocument: function (doc, type, task, only) {

        if( doc.members ) {
            doc.members.restrictionTab.save();
        }

    },

    postSaveObject: function (obj, task, only) {

        if( obj.members ) {
            obj.members.restrictionTab.save();
        }
    },

    postSaveAsset: function (assetId) {

        var asset;

        if (pimcore.globalmanager.exists('asset_' + assetId) !== false) {
            asset = pimcore.globalmanager.get('asset_' + assetId);
            if( asset.members ) {
                asset.members.restrictionTab.save();
            }

        }

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

        var isAllowed = true;

        if(type === 'object' && this.settings.restriction.allowed_objects.indexOf(obj.data.general.o_className) === -1) {
            isAllowed = false;
        } else if(type === 'page' && obj.type !== 'page') {
            isAllowed = false;
        } else if(type === 'asset' && !(obj.data.filename === 'restricted-assets' || obj.data.path.substring(0, 18) === '/restricted-assets')) {
            isAllowed = false;
        }

        if(isAllowed) {
            obj.members = {};
            var restrictionTab = new pimcore.plugin.members.document.restriction(obj);
            restrictionTab.setup(type);
        }

    }

});

new pimcore.plugin.members();