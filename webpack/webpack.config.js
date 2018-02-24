const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
    entry :{
        Caracteres:['babel-polyfill',path.resolve(__dirname,'./Catalogos/Caracteres/index.js')],
        Textos:['babel-polyfill',path.resolve(__dirname,'./Catalogos/Textos/index.js')],
        SubDocumentos:['babel-polyfill',path.resolve(__dirname,'./Catalogos/subDocumentos/index.js')],
        Acciones:['babel-polyfill',path.resolve(__dirname,'./Catalogos/Acciones/index.js')],
        Volantes:['babel-polyfill',path.resolve(__dirname,'./Volantes/volantes/index.js')],
        Diversos:['babel-polyfill',path.resolve(__dirname,'./Volantes/diversos/index.js')],
        Documentos:['babel-polyfill',path.resolve(__dirname,'./Documentos/general/index.js')],
        Irac:['babel-polyfill',path.resolve(__dirname,'./Oficios/Irac/index.js')],
        Ifa:['babel-polyfill',path.resolve(__dirname,'./Oficios/Ifa/index.js')],
        Confronta:['babel-polyfill',path.resolve(__dirname,'./Oficios/Confronta/index.js')],
        DocumentosDiversos:['babel-polyfill',path.resolve(__dirname,'./Oficios/Diversos/index.js')]

    }, 
    output:{
        filename: '[name].js',
        path: path.resolve(__dirname,'../public/js/'),
        publicPath:path.resolve(__dirname,'../public/js/')
    },
    module:{
        rules:[
            {
                test:/\.styl$/,
                use:ExtractTextPlugin.extract({
                    use:['css-loader','stylus-loader']
                })
            },
            {
                test:/\.(jpg|png|)$/,
                use:{
                    loader:'url-loader',
                    options:{
                        limit:500000
                    }
                }
            },
            {
                test:/\.js$/,
                exclude: /(node_modules|bower_components)/,
                use:{
                    loader:'babel-loader',
                    options:{
                        presets:['babel-preset-es2015'],
                        plugins:['transform-async-to-generator']
                    }
                }
            },
            {
                test:/\.html$/,
                use:'html-loader'
            }
        ]
    },
    plugins:[
        new webpack.DllReferencePlugin({
            manifest:require('./vendor-manifest.json')
        }),
        new webpack.ProvidePlugin({
            $: "jquery",
            jquery: "jQuery",
            "windows.jQuery": "jquery",
            jQuery:'jquery'
        })
    ]
}