const path = require('path');

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
};
