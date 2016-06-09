Requirements:
-------------
1. PHP >= 5.4

2. Browser with support HTML5 File API (IE 10+, Firefox 3.6+, Chrome 13+, Safari 6+, Opera 11.5+).


Installation:
-------------
1. Extract module archive in "sites/all/modules".

2. Enable module "One Click Upload".


Integrate with BUEditor:
------------------------
1. Open BUEditor config page "admin/config/content/bueditor".

2. Click "Edit" link for your use editor.

3. Add new button with code: "js: E.showFileSelectionDialog();" (do not change this code!).

4. Click "Save configuration".


Integrate with standalone CKEditor module (not Wysiwyg module):
---------------------------------------------------------------
1. Open CKEditor config page "admin/config/content/ckeditor".

2. Click "edit" link for your use editor.

3. Open section "Editor appearance", go to "Toolbar" field and drag & drop "One Click Upload" icon from "All buttons" to "Used buttons".

4. Below, in "Plugins" field, choose checkbox "One Click Upload".

5. Disable "Advanced content filter" in an appropriate section.

6. Click "Save".


Integrate to Wysiwyg module with CKEditor library:
--------------------------------------------------
1. Open Wysiwyg cofig page "admin/config/content/wysiwyg".

2. Click "Edit" link for your use editor.

3. Open fieldset "Buttons and plugins" and mark checkbox "One Click Upload".

4. Click "Save".
