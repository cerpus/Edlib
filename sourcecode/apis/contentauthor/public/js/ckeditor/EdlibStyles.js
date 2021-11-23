/**
 * Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

// This file contains style definitions that can be used by CKEditor plugins.
//
// The most common use for it is the "stylescombo" plugin which shows the Styles drop-down
// list containing all styles in the editor toolbar. Other plugins, like
// the "div" plugin, use a subset of the styles for their features.
//
// If you do not have plugins that depend on this file in your editor build, you can simply
// ignore it. Otherwise it is strongly recommended to customize this file to match your
// website requirements and design properly.
//
// For more information refer to: https://ckeditor.com/docs/ckeditor4/latest/guide/dev_styles.html#style-rules

CKEDITOR.stylesSet.add("EdlibStyles", [
    /* Block styles */

    {name: "Italic Title", element: "h2", styles: {"font-style": "italic"}},
    {name: "Subtitle", element: "h3", styles: {"color": "#aaa", "font-style": "italic"}},
    {name: "Special Container", element: "div", attributes: {"class": "specialContainer"}},
    {name: "Infobox",element: 'section',attributes: {"class": "clearfix infobox"}},
    {name: "Intro", element: "p", attributes: {"class": "ingress"}},
    {name: "Marker", element: "span", attributes: {"class": "marker"}},

    {name: "Big", element: "big"},
    {name: "Small", element: "small"},
    {name: "Typewriter", element: "tt"},

    {name: "Computer Code", element: "code"},
    {name: "Keyboard Phrase", element: "kbd"},
    {name: "Sample Text", element: "samp"},
    {name: "Variable", element: "var"},

    {name: "Deleted Text", element: "del"},
    {name: "Inserted Text", element: "ins"},

    {name: "Cited Work", element: "cite"},
    {name: "Inline Quotation", element: "q"},

    {name: "Language: RTL", element: "span", attributes: {"dir": "rtl"}},
    {name: "Language: LTR", element: "span", attributes: {"dir": "ltr"}},

    /* Object styles */
    {
        name: "Image 100%",
        element: "img",
        attributes: {"class": "img_100"}
    },
    {
        name: "Left image 25%",
        element: "img",
        attributes: {"class": "clearfix img_25 left"}
    },
    {
        name: "Left image 50%",
        element: "img",
        attributes: {"class": "img_50 left"}
    },
    {
        name: "Left image 66%",
        element: "img",
        attributes: {"class": "img_66 left"}
    },
    {
        name: "Right image",
        element: "img",
        attributes: {"class": "right"}
    },
    {
        name: "Right image 25%",
        element: "img",
        attributes: {"class": "img_25 right"}
    },
    {
        name: "Right image 50%",
        element: "img",
        attributes: {"class": "img_50 right"}
    },
    {
        name: "Right image 66%",
        element: "img",
        attributes: {"class": "img_66 right"}
    },
    {
        name: "Compact Table",
        element: "table",
        attributes: {
            cellpadding: "5",
            cellspacing: "0",
            border: "1",
            bordercolor: "#ccc"
        },
        styles: {
            "border-collapse": "collapse"
        }
    },

    {name: "Borderless Table", element: "table", styles: {"border-style": "hidden", "background-color": "#E6E6FA"}},
    {name: "Square Bulleted List", element: "ul", styles: {"list-style-type": "square"}},

    /* Widget styles */
    {name: "Featured Formula", type: "widget", widget: "mathjax", attributes: {"class": "math-featured"}},

]);

