(function IFFE() {
  function lobiNotify(type, title, msg) {
    Lobibox.notify(type, {
      sound: false,
      position: 'top right',
      delay: 1500,
      showClass: 'fadeInDown',
      title: title,
      msg: msg
    });
  }

  $("#menu_tree_item tbody").sortable({
    handle: '.tree-children-draggable',
    start: function (event, ui) {
      ui.item.data('start_pos', ui.item.index());
    },
    stop: function (event, ui) {
      var start_pos = ui.item.data('start_pos');
      if (start_pos != ui.item.index()) {
        var element = 0, prev = 0, next = 0;

        if (typeof ui.item.prev('tr').attr("data-key") !== 'undefined')
          prev = ui.item.prev('tr').attr("data-key");

        if (typeof ui.item.next('tr').attr("data-key") !== 'undefined')
          next = ui.item.next('tr').attr("data-key");

        element = ui.item.attr("data-key");
        let data = {
          prev: prev,
          element: element,
          next: next
        }

        $.ajax({
          url: '/core/menu/sort',
          type: 'POST',
          data: {
            prev: prev,
            element: element,
            next: next
          },
          success: function (res) {
            console.log(res);
            if (res == false) {
              lobiNotify('error', 'Child Hierarchy', 'Changes not Saved')
            } else {
              lobiNotify('success', 'Child Hierarchy', 'Saved');
            }
          },
          error: function (err) {
            lobiNotify('error', 'Child Hierarchy', 'Changes not Saved')
          }
        });
      }
    }
  });
})();
