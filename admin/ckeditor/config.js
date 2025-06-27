/**
 * @license Copyright (c) 2003-2025, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function(config) {
  // Bật các plugin cần thiết
  config.extraPlugins = 'fontawesome5,imageuploader,uploadfile,uploadimage,youtube,btbutton,bootstrapTabs,lineheight,format_buttons,hkemoji,html5video';

  // Cấu hình upload ảnh
  config.uploadUrl = '/kai/admin/upload-image.php';
  config.imageUploadUrl = '/kai/admin/upload-image.php'; // quan trọng!

  config.clipboard_uploadUrl = '/kai/admin/upload-image.php';

  config.filebrowserUploadUrl = '/kai/admin/upload-image.php'; // fallback cho filebrowser
  config.allowedContent = true;

  config.removeDialogTabs = 'image:advanced;link:advanced';

  config.clipboard_handleImages = true;


  // Tắt plugin export PDF nếu không dùng
  config.removePlugins = 'exportpdf';
};

