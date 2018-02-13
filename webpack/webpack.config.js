const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
    entry :{
        Catalogos:['babel-polyfill',path.resolve(__dirname,'./Catalogos/index.js')]
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