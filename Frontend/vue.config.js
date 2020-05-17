const path = require('path');
const UglifyJS = require('uglify-es');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
  devServer:{
    historyApiFallback: true,
    // contentBase: [path.join(__dirname, "public"), path.join(__dirname, "static")],
    proxy: {
      '/api': {
        target: 'http://comment.local',
      },
    },
  },
  configureWebpack: config => {
    config.plugins.push(
        new CopyWebpackPlugin([
          {
            from: path.join(__dirname, 'src/assets/js'),
            to: 'js',
            transform: function (content) {
              return UglifyJS.minify(content.toString()).code;
            }
          }
        ])
    );
  }
};
