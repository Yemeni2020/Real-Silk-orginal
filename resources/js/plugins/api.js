export default {
  install(app) {
    app.config.globalProperties.$getApiUrl = function (endpoint) {
      let basePath = '';
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        basePath = '/rsbaba'; // اسم مجلدك المحلي
      }
      let url=`${window.location.origin}${basePath}/vueAPI/${endpoint}`;
      url.replace("//","/")
      return url;
    };
  }
}
