pimcore.registerNS('pimcore.plugin.members.object.tags.multiselect');
pimcore.plugin.members.object.tags.multiselect = Class.create(pimcore.object.tags.abstract, {

    type: 'multiselect',
    allowBatchAppend: false,
    allowBatchRemove: false,

    initialize: function (data, fieldConfig) {
        this.data = data;
        this.fieldConfig = fieldConfig;
    },

    generateStoreData: function (fieldConfig) {
        return new Ext.data.Store({
            autoload: false,
            proxy: {
                type: 'ajax',
                url: '/admin/members/restriction/get-groups',
                fields: ['id', 'name'],
                reader: {
                    type: 'json',
                },
            }
        });
    },

    getGridColumnConfig: function (field) {

        return {
            text: t(field.label),
            width: 150,
            sortable: false,
            dataIndex: field.key,
            filter: this.getGridColumnFilter(field),
            getEditor: this.getWindowCellEditor.bind(this, field),
            renderer: function (key, value, metaData, record) {

                var rawValue = value,
                    formattedValue = [];

                try {
                    if (record.data.inheritedFields && record.data.inheritedFields[key] && record.data.inheritedFields[key].inherited === true) {
                        metaData.tdCls += ' grid_value_inherited';
                    }
                } catch (e) {
                    console.log(e);
                }

                if (!value) {
                    return '';
                }

                if (typeof rawValue === 'string') {
                    rawValue = value.split(',');
                }

                Ext.Array.each(rawValue, function (rowData) {
                    formattedValue.push(Ext.isObject(rowData) ? rowData.name : rowData);
                });

                return formattedValue;

            }.bind(this, field.key)
        };
    },

    getGridColumnFilter: function (field) {
        return false;
    },

    getLayoutEdit: function () {

        var options = {
            name: this.fieldConfig.name,
            triggerAction: 'all',
            editable: false,
            fieldLabel: this.fieldConfig.title,
            itemCls: 'object_field',
            valueField: 'id',
            displayField: 'name',
            width: 400,
            queryMode: 'local',
            store: this.generateStoreData(),
            listeners: {
                afterrender: function (field) {
                    field.store.load({
                        callback: function () {
                            field.setValue(this.data ? Ext.Array.map(this.data, function (row) {
                                return row.id;
                            }) : null);
                        }.bind(this)
                    });
                }.bind(this)
            }
        };

        if (this.fieldConfig.width) {
            options.width = this.fieldConfig.width;
        }

        if (this.fieldConfig.renderType !== 'tags') {
            if (this.fieldConfig.height) {
                options.height = 100;
            } else {
                options.minHeight = 150;
                options.maxHeight = 400;
            }
        }

        if (this.fieldConfig.renderType === 'tags') {
            options.queryMode = 'local';
            options.editable = true;
            this.component = Ext.create('Ext.form.field.Tag', options);
        } else {
            this.component = Ext.create('Ext.ux.form.MultiSelect', options);
        }

        return this.component;
    },

    getLayoutShow: function () {

        this.component = this.getLayoutEdit();

        this.component.on('afterrender', function () {
            this.component.disable();
        }.bind(this));

        return this.component;
    },

    getValue: function () {

        var res = [];

        if (this.isRendered()) {
            return this.component.getValue();
        }

        if (this.data) {
            res = [this.data];
        }

        return res;
    },

    getCellEditValue: function () {
        return this.getValue();
    },

    getName: function () {
        return this.fieldConfig.name;
    }
});