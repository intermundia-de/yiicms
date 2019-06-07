/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
//setTimeout(function () {

(function () {
  var choosenEditor = {};
  var pathHtml = '';
  CKEDITOR.plugins.add('link-tree', {
    init: function (editor) {
      editor.addCommand('showModal', {
        exec: function (editor) {
          LinkedTreeCommand(editor)
        }
      });
      editor.addCommand('linkTree', {
        exec: function (editor) {
          var now = new Date();
          editor.insertHtml(pathHtml);
        }
      });

      editor.ui.addButton('link-page', {
        label: 'Link Tree',
        command: 'showModal',
        toolbar: 'insert',
      });
    }
  });

  console.log("111111", CKEDITOR.config.extraPlugins);
  if (CKEDITOR.config.extraPlugins) {
    CKEDITOR.config.extraPlugins += ',link-tree';
  } else {
    CKEDITOR.config.extraPlugins = 'link-tree';
  }

  CKEDITOR.config.allowedContent = true;

  var $linkModal = $('#link-plugin-modal');
  var $linkButton = $('#link-plugin-button');
  var $linkObjectButton = $('#link-object-plugin-button');
  var $jsTree = $("#jstree-link-plugin");

  function LinkedTreeCommand(editor) {
    choosenEditor = editor;
    $linkModal.modal('show');
  }

  $linkModal.on('show.bs.modal', function () {
    $.ajax({
      url: '/base/tree-for-move',
      type: "POST",
      data: {
        key: parseInt($('#tree-modal-body').attr('data-key')),
      },
      success: function (res) {
        initTreeMove(res);
      }
    });
  });

  $linkButton.click(function () {
    var prependTo = [];
    var id = '';
    var text = choosenEditor.getSelection().getSelectedText();
    $jsTree.jstree("get_checked", this, true).forEach(function (data) {
      id = data.original.id;
      if (!text.length > 0) {
        text = data.original.text;
      }
    });


    pathHtml = '<a href="{{contentTreeId:' + id + '}}">' + text + '</a>';
    choosenEditor.execCommand('linkTree');
    $linkModal.modal('hide');
  });
  $linkObjectButton.click(function () {
    var prependTo = [];
    var id = '';
    // var text = choosenEditor.getSelection().getSelectedText();
    $jsTree.jstree("get_checked", this, true).forEach(function (data) {
      id = data.original.id;
    });


    pathHtml = '<div>{{content:' + id + '}}</div>';
    choosenEditor.execCommand('linkTree');
    $linkModal.modal('hide');
  });

  function initTreeMove(res) {
    $jsTree.jstree({
      plugins: ['checkbox', 'rules', 'types', 'wholerow'],
      checkbox: {
        "three_state": false,
      },
      core: {
        multiple: false,
        data: JSON.parse(res)
      },
      types: {
        "website": {icon: "fa fa-globe"},
        "page": {icon: "fa fa-file-powerpoint-o"},
        "video_section": {icon: "fa fa-file-video-o"},
        "content_text": {icon: "fa fa-file-text"},
        "teaser": {icon: "fa fa-file-text"},
        "section": {icon: "fa fa-folder-open-o"},
        "service": {icon: "fa fa-wrench"},
        "pharmaceutical_form": {icon: "fa fa-medkit"},
      }
    });
  }
})();

