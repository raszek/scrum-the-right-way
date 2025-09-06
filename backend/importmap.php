<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'util' => [
        'path' => './assets/util.js',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'version' => '1.11.3',
        'type' => 'css',
    ],
    'iconoir/css/iconoir-regular.min.css' => [
        'version' => '7.8.0',
        'type' => 'css',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@toast-ui/editor/dist/toastui-editor.css' => [
        'version' => '3.2.2',
        'type' => 'css',
    ],
    '@toast-ui/editor/dist/toastui-editor.min.css' => [
        'version' => '3.2.2',
        'type' => 'css',
    ],
    '@toast-ui/editor' => [
        'version' => '3.2.2',
    ],
    'prosemirror-model' => [
        'version' => '1.19.4',
    ],
    'prosemirror-view' => [
        'version' => '1.32.7',
    ],
    'prosemirror-transform' => [
        'version' => '1.9.0',
    ],
    'prosemirror-state' => [
        'version' => '1.4.3',
    ],
    'prosemirror-keymap' => [
        'version' => '1.2.2',
    ],
    'prosemirror-commands' => [
        'version' => '1.5.2',
    ],
    'prosemirror-inputrules' => [
        'version' => '1.3.0',
    ],
    'prosemirror-history' => [
        'version' => '1.3.2',
    ],
    'orderedmap' => [
        'version' => '2.1.1',
    ],
    'w3c-keyname' => [
        'version' => '2.2.8',
    ],
    'rope-sequence' => [
        'version' => '1.3.4',
    ],
    'prosemirror-view/style/prosemirror.min.css' => [
        'version' => '1.32.7',
        'type' => 'css',
    ],
    '@stimulus-components/sortable' => [
        'version' => '5.0.1',
    ],
    'sortablejs' => [
        'version' => '1.15.2',
    ],
    '@rails/request.js' => [
        'version' => '0.0.8',
    ],
    '@stimulus-components/dialog' => [
        'version' => '1.0.1',
    ],
    'tom-select' => [
        'version' => '2.3.1',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'stimulus-use' => [
        'version' => '0.52.2',
    ],
    'stimulus-use-actions' => [
        'version' => '0.1.0',
    ],
    'glightbox' => [
        'version' => '3.3.0',
    ],
    'glightbox/dist/css/glightbox.min.css' => [
        'version' => '3.3.0',
        'type' => 'css',
    ],
    'chart.js' => [
        'version' => '4.4.9',
    ],
    '@kurkle/color' => [
        'version' => '0.3.4',
    ],
    'cropperjs' => [
        'version' => '2.0.1',
    ],
    '@cropper/utils' => [
        'version' => '2.0.1',
    ],
    '@cropper/elements' => [
        'version' => '2.0.1',
    ],
    '@cropper/element' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-canvas' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-image' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-shade' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-handle' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-selection' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-grid' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-crosshair' => [
        'version' => '2.0.1',
    ],
    '@cropper/element-viewer' => [
        'version' => '2.0.1',
    ],
    'date-fns' => [
        'version' => '4.1.0',
    ],
];
