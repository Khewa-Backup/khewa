(function () {
  document.addEventListener('submit', function (event) {
    var form = event.target;

    if (form && form.classList && form.classList.contains('pe-search')) {
      document.body.classList.add('pe-loading');
    }
  });
})();
