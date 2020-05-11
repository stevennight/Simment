module.exports = {
    module: {
        rules: [
            {
                test: /\.styl(us)?$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    'stylus-loader'
                ]
            }
        ]
    }
}