(function () {
  var $a = $('a[href^="{{contentTreeId:"]');
  var ids = [];
  $a.each(function (index, el) {
    var $this = $(el);
    var href = $this.attr('href');
    var id = href.replace('{{', '').replace('}}', '').split(':')[1];
    ids.push(id)
  });
})();
