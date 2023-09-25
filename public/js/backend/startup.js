class MembersCore {
    constructor() {
        this.ready = false;
        this.settings = {};
        this.dataQueue = [];
    }

    init() {
        Ext.Ajax.request({
            url: '/admin/members/restriction/get-global-settings',
            success: function (response) {
                const resp = Ext.decode(response.responseText);

                this.settings = resp.settings;
                this.ready = true;
                this.processQueue();

            }.bind(this)
        });
    }

    postOpenDocument(ev) {

        const document = ev.detail.document;

        if (this.ready) {
            this.processElement(document, 'page');
        } else {
            this.addElementToQueue(document, 'page');
        }
    }

    postOpenObject(ev) {

        const object = ev.detail.object;

        if (this.ready) {
            this.processElement(object, 'object');
        } else {
            this.addElementToQueue(object, 'object');
        }
    }

    postOpenAsset(ev) {

        const asset = ev.detail.asset;

        if (this.ready) {
            this.processElement(asset, 'asset');
        } else {
            this.addElementToQueue(asset, 'asset');
        }
    }

    postSaveDocument(ev) {

        const document = ev.detail.document;

        if (document.members) {
            document.members.restrictionTab.save();
        }
    }

    postSaveObject(ev) {

        const object = ev.detail.object;

        if (object.members) {
            object.members.restrictionTab.save();
        }
    }

    postSaveAsset(ev) {

        let asset,
            assetId = ev.detail.id;

        if (pimcore.globalmanager.exists('asset_' + assetId) !== false) {
            asset = pimcore.globalmanager.get('asset_' + assetId);
            if (asset.members) {
                asset.members.restrictionTab.save();
            }
        }
    }

    addElementToQueue(obj, type) {
        this.dataQueue.push({'obj': obj, 'type': type});
    }

    processQueue() {

        if (this.dataQueue.length === 0) {
            return;
        }

        Ext.each(this.dataQueue, function (data) {

            const obj = data.obj;
            const type = data.type;

            this.processElement(obj, type);

        }.bind(this));

        this.dataQueue = {};
    }

    processElement(obj, type) {

        let isAllowed = true,
            restrictionTab;

        if (this.settings.restriction.enabled === false) {
            return false;
        }

        if (type === 'object' && this.settings.restriction.allowed_objects.indexOf(obj.data.general.className) === -1) {
            isAllowed = false;
        } else if (type === 'page' && ['page', 'link'].indexOf(obj.type) === -1) {
            isAllowed = false;
        } else if (type === 'asset' && !(obj.data.filename === 'restricted-assets' || obj.data.path.substring(0, 18) === '/restricted-assets')) {
            isAllowed = false;
        }

        if (isAllowed) {
            obj.members = {};
            restrictionTab = new pimcore.plugin.members.document.restriction(obj);
            restrictionTab.setup(type);
        }
    }
}

const membersCoreHandler = new MembersCore();

document.addEventListener(pimcore.events.pimcoreReady, membersCoreHandler.init.bind(membersCoreHandler));
document.addEventListener(pimcore.events.postOpenDocument, membersCoreHandler.postOpenDocument.bind(membersCoreHandler));
document.addEventListener(pimcore.events.postOpenObject, membersCoreHandler.postOpenObject.bind(membersCoreHandler));
document.addEventListener(pimcore.events.postOpenAsset, membersCoreHandler.postOpenAsset.bind(membersCoreHandler));
document.addEventListener(pimcore.events.postSaveDocument, membersCoreHandler.postSaveDocument.bind(membersCoreHandler));
document.addEventListener(pimcore.events.postSaveObject, membersCoreHandler.postSaveObject.bind(membersCoreHandler));
document.addEventListener(pimcore.events.postSaveAsset, membersCoreHandler.postSaveAsset.bind(membersCoreHandler));
