const path = require('path')
const webpack = require('webpack')
module.exports = {
    entry :{
        vendor: ['jquery','jquery-confirm','jquery-validation','jquery-ui-browserify','jquery-ui']
    }, 
    output:{
        filename: '[name].js',
        path: path.resolve(__dirname,'../public/js/'),
        library:'[name]'
    },
    plugins:[
       new webpack.DllPlugin({
           path:path.resolve(__dirname,'[name]-manifest.json'),
           name:'[name]'
       }),
        new webpack.ProvidePlugin({
            $: "jquery",
            jquery: "jQuery",
            "windows.jQuery": "jquery",
            jQuery:'jquery'
        })
    ]
}