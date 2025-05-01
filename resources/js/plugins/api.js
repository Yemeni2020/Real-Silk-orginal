export default {
  install(app) {
    app.config.globalProperties.$getApiUrl = function (endpoint) {
      let basePath = '';
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        basePath = '/rsbaba'; // اسم مجلدك المحلي
      }
      let url = `${basePath}/vueAPI/${endpoint}`;
      url = window.location.origin + url.replace("//", "/");
      return url;
    };

    app.config.globalProperties.$getUrl = function (endpoint) {
      let basePath = '';
      if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        basePath = '/rsbaba'; // اسم مجلدك المحلي
      }
      let url = `${basePath}/${endpoint}`;
      url = window.location.origin + url.replace("//", "/");
      return url;
    };

    // ✅ دالة الترجمة العالمية
    app.config.globalProperties.$translate = async function (key, fallback = null) {
      try {
        const res = await fetch(this.$getApiUrl(`translate?key=${encodeURIComponent(key)}`));
        const data = await res.json();
        return data.translation;
      } catch (e) {
        console.error('Translation error:', e);
        return fallback ?? key;
      }
    };
  }
};
