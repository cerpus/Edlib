(function ($) {

  function isReady(){
    return window.MathJax !== undefined;
  }

  function loadedAndReady(){
      // Hide annoying processing messages
      MathJax.Hub.Config({messageStyle: 'none'});

      // Find H5P content
      $('.h5p-content').each(function (i, e) {
          var doJax = function (node) {
              if( node && !node.getAttribute('data-math-processed') && node.parentNode){
                  node.setAttribute('data-math-processed', true);
                  MathJax.Hub.Queue(['Typeset', MathJax.Hub, node.parentNode]);
              }
          };
          var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
          if (!MutationObserver) {
              var check = function () {
                  $('math', e).each(function (j, m) {
                      doJax(m);
                  });
                  checkInterval = setTimeout(check, 2000);
              };
              var checkInterval = setTimeout(check, 2000);
          }
          else {
              var running = false;
              var limitedResize = function () {
                  if (!running) {
                      running = setTimeout(function () {
                          $('math', e).each(function (j, m) {
                              doJax(m);
                          });
                          running = null;
                      }, 500); // 2 fps cap
                  }
              };

              var observer = new MutationObserver(function (mutations) {
                  for (var i = 0; i < mutations.length; i++) {
                      if (mutations[i].addedNodes.length) {
                          limitedResize();
                          return;
                      }
                  }
              });
              observer.observe(e, {
                  childList: true,
                  subtree: true
              });
          }
      });
  };

  $(document).ready(function () {
      var attempts = 0;
      var loaderInterval = setInterval(function(){
          if( isReady() ){
              clearInterval(loaderInterval);
              loadedAndReady();
          }
          if( attempts >= 20){
              clearInterval(loaderInterval);
          }
          attempts++;
      }, 50);
  });
})(H5P.jQuery);
