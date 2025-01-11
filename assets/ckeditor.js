import { ClassicEditor, AutoLink, Autosave, Bold, Essentials, Italic, Link, Paragraph } from 'ckeditor5';


const LICENSE_KEY =
    'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3Mzc4NDk1OTksImp0aSI6IjgxYzQzYjk0LWVkNzctNDQzMi05YjFhLTA0OTRiODYzNjA4ZiIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6IjExZDJiNjJhIn0.F1Q0VbJdEmK0O1ibKR1FzT-fH7vEqbxpsOyjQmL4Ehx2WH7c12gys0aempC6YgQJMZUCs0u0eFIh_bXSzczHYQ';

const editorConfig = {
    toolbar: {
        items: ['bold', 'italic', '|', 'link'],
        shouldNotGroupWhenFull: false
    },
    plugins: [AutoLink, Autosave, Bold, Essentials, Italic, Link, Paragraph],
    initialData:
        '',
    licenseKey: LICENSE_KEY,
    link: {
        addTargetToExternalLinks: true,
        defaultProtocol: 'https://',
        decorators: {
            toggleDownloadable: {
                mode: 'manual',
                label: 'Downloadable',
                attributes: {
                    download: 'file'
                }
            }
        }
    },
    placeholder: 'Type or paste your content here!'
};



ClassicEditor.create(document.querySelector('#sandbox_content, #project_content, #comment_content'), editorConfig);
