const path = require('path')
const webpack = require('webpack')
module.exports = {
    entry :{
        Catalogos:[path.resolve(__dirname,'./Catalogos/index.js')]
    }, 
    output:{
        filename: '[name].js',
        path: path.resolve(__dirname,'../public/js_webpack/'),
        publicPath:path.resolve(__dirname,'../public/js_webpack/')
    },
    module:{
        rules:[
            {
                test:/\.styl$/,
                loader:'stylus-loader'
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
        })
    ]
}