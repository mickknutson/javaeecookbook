/*
 *
 */
Ext.require([
    'Ext.form.Panel',
    'Ext.form.FieldSet',
    'Ext.field.Select',
    'Ext.Button',
    'Ext.Container',

    'Ext.util.JSONP',

    'Ext.data.Store'
]);

Ext.setup({
    icon: 'icon.png',
    tabletStartupScreen: 'tablet_startup.png',
    phoneStartupScreen: 'phone_startup.png',
    glossOnIcon: false,
    onReady: function() {
        var form;

        Ext.regModel('Calculation', {
            extend: 'Ext.data.Model',

            fields: [
                {name: 'abv_r',     type: 'float'},
                {name: 'volume_r',  type: 'float'},
                {name: 'price_r',   type: 'float'},
                {name: 'value_r',   type: 'float'},
                {name: 'score',     type: 'float'}
            ]
        });

        //var store = Ext.create('Ext.data.Store', {
        Ext.regStore('ResultsStore', {
            model: 'Calculation',
            sorters: [
                {
                    property: 'score',
                    direction: 'DESC'
                }
            ],
            proxy: {
                type: 'localstorage',
                id: 'calculations-app-localstore'
            },
            // TODO: remove this data after testing.
            data: [
                { score: 5, abv_r: 3.2, volume_r: 16, price_r: 7.99, value_r: 0.501 }
            ]
        });

        var resultTemplate = new Ext.XTemplate(
            '<tpl for=".">' +
                '<li id="results" class="results">' +
                '<h3>ABV Score: {score}</h3>' +
                '<span>{abv_r}% abv</span> | ' +
                '<span>{volume_r} oz</span> | ' +
                '<span>Cost ${price_r}</span> | ' +
                '<span>Value ${value_r} /per %abv /per oz</span>' +
                '</li>' +
                '</tpl>'
        );

        var resultsList = new Ext.List({
            id: 'resultsList',
            store: 'ResultsStore',
            itemTpl: resultTemplate
            /*itemTpl: '<div class = "list-item-score" >ABV Score: {score}</div>' +
                '<div class="list-item-results">'+
                '<span>{abv_r}% abv</span> | '+
                '<span>{volume_r} oz</span> | ' +
                '<span>Cost ${price_r}</span> | ' +
                '<span>Value ${value_r} /per %abv /per oz</span>' +
                '</div>'*/
        });

        var resultsListContainer = new Ext.Panel({
            id: 'resultsListContainer',
            layout: 'fit',
            html: 'This is the calculation list container',
            items: [resultsList]
        });

        var makeJSONPRequest = function() {
            var fields = form.getValues();

            Ext.getBody().mask('Loading...', 'x-mask-loading', false);
            Ext.util.JSONP.request({
                url: 'http://baselogic.com/abv/calculateCallback.php',
                callbackKey: 'callback',
                params: {
                    key: '23f6a0ab24185952101705',
                    format: 'json',
                    abv_r: fields['abv_r'],
                    volume_r: fields['volume_r'],
                    price_r: fields['price_r']
                },
                callback: function(result) {
                    alert('0');
                    store.insert(0,
                        new Ext.data.Record(
                            {
                                'score': result.score,
                                'value_r': result.value_r,
                                'abv_r': result.abv_r,
                                'volume_r': result.volume_r,
                                'price_r': result.price_r
                            }
                            , '-1'
                        )
                    );
                }
            });
        };

        var formBase = {
            standardSubmit: false,
            items: [
                {
                    xtype: 'fieldset',
                    title: 'Alcohol By Value',
                    instructions: 'Please enter the information above.',
                    defaults: {
                        required  : true,
                        labelAlign: 'left',
                        labelWidth: '40%'
                    },
                    items: [
                        {
                            xtype: 'numberfield',
                            name: 'abv_r',
                            label: 'ABV (%)',
                            min: "0.0",
                            max: "100.0",
                            step: "0.1"
                        },
                        {
                            xtype: 'selectfield',
                            name: 'volume_r',
                            label: 'Volume',
                            options: [
                                {text: 'Select the volume',  value: '0'},
                                {text: '8 oz', value: '8'},
                                {text: '10 oz', value: '10'},
                                {text: '12 oz', value: '12'},
                                {text: '16 oz', value: '16'},
                                {text: '24 oz', value: '24'}
                            ]
                        },
                        {
                            xtype: 'numberfield',
                            name: 'price_r',
                            label: 'Price',
                            min: "0.0",
                            max: "100.0",
                            step: "0.1"
                        }
                    ]
                },
                {
                    xtype: 'toolbar',
                    docked: 'bottom',
                    items: [
                        {xtype: 'spacer'},
                        {
                            text: 'Reset',
                            handler: function() {
                                form.reset();
                            }
                        },
                        {
                            text: 'Calculate',
                            handler: makeJSONPRequest
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    title: 'Results',
                    items: [
                        resultsListContainer, {
                            flex: 5,
                            html: 'results'
                        }
                    ]
                }
            ]
        };

        if (Ext.os.deviceType == 'Phone') {
            Ext.apply(formBase, {
                xtype: 'formpanel',
                autoRender: true
            });

            form = Ext.Viewport.add(formBase);
        }
        else {
            Ext.apply(formBase, {
                autoRender   : true,
                modal        : true,
                hideOnMaskTap: false,
                height       : 505,
                width        : 480,
                centered     : true,
                fullscreen   : true
            });

            form = Ext.create('Ext.form.Panel', formBase);
            form.show();
        }

        /*var content = Ext.create('Ext.Panel',{
            fullscreen: true,
            scroll: 'vertical',
            data: null,
            tpl: resultTemplate
        });*/

        //content.setData(ResultsStore);

        container = new Ext.Container({
            fullscreen: true,
            defaults: {
                xtype: 'container'
            },
            items: [{
                docked: 'top',
                height: 50,
                style: 'background-color: green',
                xtype: 'formpanel'
            }, {
                docked: 'left',
                width: 50,
                style: 'background-color: orange'
            }, {
                docked: 'right',
                width: 50,
                style: 'background-color: gray'
            }, {
                docked: 'bottom',
                height: 50,
                style: 'background-color: purple'
            }]
        });


    }
});