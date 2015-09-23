/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

// CKEDITOR.editorConfig = function( config )
// {
// 	// Define changes to default configuration here. For example:
// 	// config.language = 'fr';
// 	// config.uiColor = '#AADC6E';
// };

CKEDITOR.editorConfig = function( config )
{
//     config.toolbar = 'Full';
//     config.toolbar_Full =
//     [
//         ['Source','-','Save','NewPage','Preview','-','Templates'],
//         ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
//         ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
//         ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
//         '/',
//         ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
//         ['BulletedList','NumberedList','-','Outdent','Indent','Blockquote','CreateDiv'],
//         ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
//         ['Link','Unlink','Anchor'],
//         ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
//         '/',
//         ['Styles','Format','Font','FontSize'],
//         ['TextColor','BGColor'],
//         ['Maximize', 'ShowBlocks','-','About']
//     ];
    
    config.toolbar_ToolbarA =
    [
        [
         'Bold','Italic','Underline','Strike','Subscript','Superscript',
         'NumberedList','BulletedList','Outdent','Indent','Blockquote',
         'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','TextColor','BGColor',
         'Link','Unlink','Anchor','Image','Table','HorizontalRule','SpecialChar'
        ],
        '/',
        ['Styles','Format'],
        ['Source','Undo','Redo','Cut','Copy','Paste','PasteText','PasteFromWord',
         'Find','Replace','RemoveFormat',
         'Maximize','About']
        
    ];
    
    
     // nastaveni tollbaru
     config.toolbar = 'ToolbarA';

     // nahrazovani diakritiky entitamy
     config.entities = false;
     config.entities_greek = false;

     // filebrowser
     config.filebrowserBrowseUrl = '/admin/filemanager/index.php';
};
