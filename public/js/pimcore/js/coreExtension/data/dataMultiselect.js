pimcore.registerNS('pimcore.plugin.members.object.classes.data.dataMultiselect');
pimcore.plugin.members.object.classes.data.dataMultiselect = Class.create(pimcore.object.classes.data.data, {

    type: 'multiselect',

    allowIn: {
        object: true,
        objectbrick: false,
        fieldcollection: false,
        localizedfield: false,
        classificationstore: false,
        block: false,
        encryptedField: false
    },

    initialize: function (treeNode, initData) {
        this.initData(initData);
        this.treeNode = treeNode;
    },

    getLayout: function ($super) {

        $super();

        this.specificPanel.removeAll();
        this.specificPanel.add([
            {
                xtype: 'textfield',
                fieldLabel: t('width'),
                name: 'width',
                value: this.datax.width ? this.datax.width : null
            },
            {
                xtype: 'displayfield',
                hideLabel: true,
                value: t('width_explanation')
            },
            {
                xtype: 'textfield',
                fieldLabel: t('height'),
                name: 'height',
                value: this.datax.height ? this.datax.height : null
            },
            {
                xtype: 'displayfield',
                hideLabel: true,
                value: t('height_explanation')
            },
            {
                xtype: 'combo',
                fieldLabel: t('multiselect_render_type'),
                name: 'renderType',
                itemId: 'renderType',
                mode: 'local',
                value: this.datax.renderType ? this.datax.renderType : 'list',
                triggerAction: 'all',
                editable: false,
                forceSelection: true,
                store: [
                    ['list', 'List'],
                    ['tags', 'Tags']
                ],
            }
        ]);

        return this.layout;
    },

    applySpecialData: function (source) {

        if (!source.datax) {
            return;
        }

        if (!this.datax) {
            this.datax = {};
        }

        Ext.apply(this.datax, {
            width: source.datax.width,
            height: source.datax.height,
            renderType: source.datax.renderType
        });
    }
});
