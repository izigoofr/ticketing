import {
    ClassicEditor,
    AutoLink,
    Autosave,
    Bold,
    Essentials,
    Italic,
    Link,
    Paragraph,
    Underline, // Plugin pour souligner
    Font, // Plugin pour styles, tailles, et couleurs
    ImageBlock,
    ImageCaption,
    ImageInline,
    ImageInsert,
    ImageInsertViaUrl,
    ImageStyle,
    ImageToolbar,
    ImageSizeAttributes,
    ImageUpload,
    ImageResize,
    SimpleUploadAdapter,
    Alignment // Plugin pour alignement
} from 'ckeditor5';

const LICENSE_KEY =
    'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3Mzc4NDk1OTksImp0aSI6IjgxYzQzYjk0LWVkNzctNDQzMi05YjFhLTA0OTRiODYzNjA4ZiIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6IjExZDJiNjJhIn0.F1Q0VbJdEmK0O1ibKR1FzT-fH7vEqbxpsOyjQmL4Ehx2WH7c12gys0aempC6YgQJMZUCs0u0eFIh_bXSzczHYQ';

const editorConfig = {
    toolbar: {
        items: [
            'bold',
            'italic',
            'underline', // Bouton pour souligner
            '|',
            'fontSize', // Taille de police
            'fontColor', // Couleur du texte
            'fontBackgroundColor', // Couleur d’arrière-plan
            '|',
            'alignment', // Options d'alignement
            'link',
            '|',
            'undo',
            'redo',
            'insertImage'
        ],
        shouldNotGroupWhenFull: false
    },
    plugins: [
        AutoLink,
        Autosave,
        Bold,
        Essentials,
        Italic,
        Link,
        Paragraph,
        Underline, // Plugin ajouté pour souligner
        Font, // Plugin ajouté pour styles et couleurs
        ImageBlock,
        ImageCaption,
        ImageInline,
        ImageInsertViaUrl,
        ImageStyle,
        ImageToolbar,
        ImageInsert,
        ImageSizeAttributes,
        ImageUpload,
        SimpleUploadAdapter,
        ImageResize,
        Alignment // Plugin pour alignement
    ],
    initialData: '',
    licenseKey: LICENSE_KEY,
    fontSize: {
        options: [
            'tiny',
            'small',
            'default',
            'big',
            'huge'
        ]
    },
    fontColor: {
        colors: [
            {
                color: 'hsl(0, 0%, 0%)',
                label: 'Noir'
            },
            {
                color: 'hsl(0, 75%, 60%)',
                label: 'Rouge'
            },
            {
                color: 'hsl(30, 75%, 60%)',
                label: 'Orange'
            },
            {
                color: 'hsl(60, 75%, 60%)',
                label: 'Jaune'
            },
            {
                color: 'hsl(120, 75%, 60%)',
                label: 'Vert'
            },
            {
                color: 'hsl(180, 75%, 60%)',
                label: 'Cyan'
            },
            {
                color: 'hsl(240, 75%, 60%)',
                label: 'Bleu'
            },
            {
                color: 'hsl(300, 75%, 60%)',
                label: 'Violet'
            }
        ],
        columns: 5 // Organiser les couleurs en colonnes
    },
    fontBackgroundColor: {
        colors: [
            {
                color: 'hsl(0, 0%, 100%)',
                label: 'Blanc'
            },
            {
                color: 'hsl(0, 0%, 0%)',
                label: 'Noir'
            },
            {
                color: 'hsl(0, 75%, 60%)',
                label: 'Rouge'
            },
            {
                color: 'hsl(30, 75%, 60%)',
                label: 'Orange'
            },
            {
                color: 'hsl(60, 75%, 60%)',
                label: 'Jaune'
            },
            {
                color: 'hsl(120, 75%, 60%)',
                label: 'Vert'
            }
        ],
        columns: 5
    },
    link: {
        addTargetToExternalLinks: true,
        defaultProtocol: 'https://',
        decorators: {
            toggleDownloadable: {
                mode: 'manual',
                label: 'Téléchargeable',
                attributes: {
                    download: 'file'
                }
            }
        }
    },
    placeholder: 'Tapez ou collez votre contenu ici !',
    simpleUpload: {
        uploadUrl: '/sandbox/file/upload',
    }

};

ClassicEditor.create(
    document.querySelector('#sandbox_content, #project_content, #comment_content'),
    editorConfig
);
