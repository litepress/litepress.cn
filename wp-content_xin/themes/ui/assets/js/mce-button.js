$(function () {
    tinymce.PluginManager.add('youran_button', function (editor, url) {
        editor.addButton('youran_button', {
            text: '',
            icon: "wp_code",
            onclick: function () {
                editor.windowManager.open({
                    title: '插入高亮代码',
                    minWidth : 700,
                    body : [
                        /*{
                            type : 'listbox',
                            name : 'lang',
                            label: 'Text Box',
                            value: '30'
                        },*/
                        {
                            type : 'textbox',
                            name : 'code',
                            label: '高亮代码',
                            multiline : true,
                            minHeight : 200,

                        }
                    ],
                    onsubmit: function (e) {
                        var code = e.data.code.replace(/\r\n/g, '\n'),
                            tag = 'code';

                        code =  tinymce.html.Entities.encodeAllRaw(code);

                        var sp = (e.data.addspaces ? '&nbsp;' : '');

                        editor.insertContent(sp + '<pre><code>' + code + '</code>\n</pre>' + sp + '<p>\n</p>');
                    }
                });
            }
        });
    })
})