export default {
  install(app) {
    app.config.globalProperties.$getApiUrl = function (endpoint) {
      let basePath = '';
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        basePath = '/rsbaba'; // اسم مجلدك المحلي
      }
      let url=`${basePath}/vueAPI/${endpoint}`;
      url =window.location.origin+url.replace("//","/")
      return url;
    };
    app.config.globalProperties.$getUrl = function (endpoint) {
      let basePath = '';
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        basePath = '/rsbaba'; // اسم مجلدك المحلي
      }
      let url=`${basePath}/${endpoint}`;
      url =window.location.origin+url.replace("//","/")
      return url;
    };
  }
}
