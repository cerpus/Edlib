import {
    Alignment,
    AutoLink,
    BlockQuote,
    Bold,
    Code,
    CodeBlock,
    Essentials,
    FontBackgroundColor,
    FontColor,
    FontFamily,
    FontSize,
    GeneralHtmlSupport,
    Heading,
    HorizontalLine,
    Image,
    Italic,
    Link,
    List,
    Paragraph,
    RemoveFormat,
    Strikethrough,
    Subscript,
    Superscript,
    Table,
    TableCaption,
    TableCellProperties,
    TableColumnResize,
    TableProperties,
    TableToolbar,
    TextPartLanguage,
    Underline,
} from 'ckeditor5';
import MathType from '@wiris/mathtype-ckeditor5/dist/index.js';

const defaultTags = ['strong', 'em', 'del', 'h2', 'h3', 'a', 'ul', 'ol', 'table', 'hr'];

/**
 * Check if the provided button is enabled by config.
 *
 * @param {string} button
 * @return {{extraPlugins: [], plugins: [], alignment: {options: string[]}, toolbar: {items: string[]}, language: {}}}
 */

export default ({ font, tags: wantedTags, enterMode }) => {
    const tags = new Set(wantedTags ?? defaultTags);
    const config = {
        extraPlugins: [],
        plugins: [Essentials, Paragraph],
        alignment: { options: ["left", "center", "right"] },
        toolbar: {
            items: [],
        },
        language: {},
    };

    const hasTag = tag => tags.has(tag);
    const hasTags = check => check.some(hasTag);
    const inButtons = button => {
        return (
            H5PIntegration.editor !== undefined &&
            H5PIntegration.editor.wysiwygButtons !== undefined &&
            H5PIntegration.editor.wysiwygButtons.includes(button)
        );
    }

    tags.add('br');

    // Basic styles
    const basicstyles = [];
    const basicstylesPlugins = [];
    if (hasTags(["strong", "b"])) {
        basicstyles.push('bold');
        basicstylesPlugins.push(Bold);
        tags.add("strong");
    }
    if (hasTags(["em", "i"])) {
        // Use <em> elements for italic text instead of CKE's default <i>
        // Has to be a plugin to work
        const ItalicAsEmPlugin = function (editor) {
            editor.conversion.for('downcast').attributeToElement({
                model: 'italic',
                view: 'em',
                converterPriority: 'high',
            });
        }

        basicstyles.push('italic');
        basicstylesPlugins.push(Italic);
        config.extraPlugins.push(ItalicAsEmPlugin);
        tags.add("i");
    }
    if (hasTag("u")) {
        basicstyles.push('underline');
        basicstylesPlugins.push(Underline);
        tags.add("u");
    }
    if (hasTags(["strike", "del", "s"])) {
        basicstyles.push('strikethrough');
        basicstylesPlugins.push(Strikethrough);
        tags.add("strike")
            .add("del")
            .add("s");
    }
    if (hasTag("sub")) {
        basicstyles.push("subscript");
        basicstylesPlugins.push(Subscript);
    }
    if (hasTag("sup")) {
        basicstyles.push("superscript");
        basicstylesPlugins.push(Superscript);
    }
    if (basicstyles.length > 0) {
        basicstyles.push('|', 'removeFormat');
        basicstylesPlugins.push(RemoveFormat);
        config.plugins.push(...basicstylesPlugins);
        config.toolbar.items.push(...basicstyles);
    }

    // Alignment is added to all wysiwygs
    config.plugins.push(Alignment);
    config.toolbar.items.push('|', 'alignment');

    // Paragraph styles
    const paragraph = [];
    const paragraphPlugins = [];
    if (hasTags(["ul", "ol"])) {
        paragraphPlugins.push(List);
    }
    if (hasTag("ul")) {
        paragraph.push("bulletedList");
        tags.add("li");
    }
    if (hasTag("ol")) {
        paragraph.push("numberedList");
        tags.add("li");
    }
    if (hasTag("blockquote")) {
        paragraph.push("blockquote");
        paragraphPlugins.push(BlockQuote);
    }
    if (inButtons('language')) {
        tags.add('span');
        paragraph.push('textPartLanguage');
        paragraphPlugins.push(TextPartLanguage);
        if (H5PIntegration?.editor?.textPartLanguages) {
            config.language.textPartLanguage = H5PIntegration.editor.textPartLanguages;
        }
    }
    if (paragraph.length > 0) {
        config.plugins.push(...paragraphPlugins);
        config.toolbar.items.push(...paragraph);
    }

    // Links.
    if (hasTag("a")) {
        const items = ["link"];
        config.plugins.push(Link, AutoLink);
        config.toolbar.items.push("|", ...items);
        config.link = {
            // Automatically add protocol if not present
            defaultProtocol: 'https://',
            // Give the author the option to choose how to open
            decorators: {
                openInNewTab: {
                    mode: 'manual',
                    label: ns.t('core', 'openInNewTab'),
                    defaultValue: true,  // This option will be selected by default.
                    attributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer'
                    }
                }
            }
        }
    }

    // Inserts
    const inserts = [];
    const insertsPlugins = [];
    if (hasTag('img')) {
        // TODO: Include toolbar functionality to insert and edit images
        // For now, we just include the plugin to prevent data loss
        insertsPlugins.push(Image);
    }
    if (hasTag("hr")) {
        inserts.push("horizontalLine");
        insertsPlugins.push(HorizontalLine);
    }
    if (hasTag('code')) {
        if (inButtons('inlineCode')) {
            inserts.push('code');
            insertsPlugins.push(Code);
        }
        if (hasTag('pre') && inButtons('codeSnippet')) {
            inserts.push('codeBlock');
            insertsPlugins.push(CodeBlock);
        }
    }
    if (inserts.length > 0) {
        config.toolbar.items.push("|", ...inserts);
    }
    if (insertsPlugins.length > 0) {
        config.plugins.push(...insertsPlugins);
    }

    if (hasTag("table")) {
        config.toolbar.items.push("insertTable");
        config.plugins.push(
            Table,
            TableToolbar,
            TableProperties,
            TableCellProperties,
            TableColumnResize,
            TableCaption,
        );
        config.table = {
            contentToolbar: [
                'toggleTableCaption',
                'tableColumn',
                'tableRow',
                'mergeTableCells',
                'tableProperties',
                'tableCellProperties'
            ],
            tableProperties: {
                defaultProperties: {
                    borderStyle: 'underline',
                    borderWidth: '0.083em',
                    borderColor: '#494949',
                    padding: '0',
                    alignment: 'left'
                }
            },
            tableCellProperties: {
                defaultProperties: {
                    borderStyle: 'underline',
                    borderWidth: '0.083em',
                    borderColor: '#494949',
                    padding: '1px'
                }
            }
        }
        tags.add("tr")
            .add("td")
            .add("th")
            .add("colgroup")
            .add("col")
            .add("thead")
            .add("tbody")
            .add("tfoot")
            .add("figure")
            .add("figcaption");
    }

    // Add dropdown to toolbar if formatters in tags (h1, h2, etc).
    const formats = [];
    for (let index = 1; index < 7; index++) {
        if (hasTag('h' + index)) {
            formats.push({ model: 'heading' + index, view: 'h' + index, title: 'Heading ' + index, class: 'ck-heading_heading' + index });
        }
    }

    if (hasTag('pre')) {
        formats.push({ model: 'formatted', view: 'pre', title: 'Formatted', class: 'ck-heading_formatted' });
    }

    // if (hasTags("address")) formats.push("address"); // TODO: potential data loss
    if (formats.length > 0 || hasTags(['p', 'div'])) {
        // If the formats are shown, always have a paragraph
        formats.push({ model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' });
        tags.add("p");
        enterMode = "p";
        config.heading = {options: formats};
        config.plugins.push(Heading);
        config.toolbar.items.push('heading');
    }

    if (font !== undefined) {
        // Create wrapper for text styling options
        const styles = [];
        const colors = [];
        tags.add('span');

        /**
         * Help set specified values for property.
         *
         * @private
         * @param {Array} values list
         * @param {string} prop Property name
         */
        const setValues = function (values, prop) {
            let options = [];
            for (let i = 0; i < values.length; i++) {
                options.push(values[i]);
            }
            config[prop] = { options: options };
        };

        // Font family chooser
        if (font.family) {
            styles.push('fontFamily');
            config.plugins.push(FontFamily);

            let fontFamilies = [
                'default',
                'Arial, Helvetica, sans-serif',
                'Comic Sans MS, Cursive, sans-serif',
                'Courier New, Courier, monospace',
                'Georgia, serif',
                'Lucida Sans Unicode, Lucida Grande, sans-serif',
                'Tahoma, Geneva, sans-serif',
                'Times New Roman, Times, serif',
                'Trebuchet MS, Helvetica, sans-serif',
                'Verdana, Geneva, sans-serif'
            ]

            // If custom fonts are set, use those
            if (font.family instanceof Array) {
                fontFamilies = ['default', ...font.family.map(font => (
                    font.label + ', ' + font.css
                ))];
            }

            setValues(fontFamilies, 'fontFamily');
            config.fontFamily.supportAllValues = true;
        }

        // Font size chooser
        if (font.size) {
            styles.push('fontSize');
            config.plugins.push(FontSize);

            let fontSizes = [];
            const convertToEm = (percent) => parseFloat(percent) / 100 + 'em';
            if (Array.isArray(font.size)) {
                // Use specified sizes
                fontSizes = font.size.map(size => ({
                    title: size.label,
                    model: convertToEm(size.css)
                }));
            } else {
                // Standard font sizes that are available
                fontSizes = [
                    'Default', '50%', '56.25%', '62.5%', '68.75%', '75%', '87.5%',
                    '100%', '112.5%', '125%', '137.5%', '150%', '162.5%', '175%',
                    '225%', '300%', '450%'
                ].map(percent => ({
                    title: percent,
                    model: percent === 'Default' ? '1em' : convertToEm(percent)
                }));
            }

            setValues(fontSizes, 'fontSize');
        }

        /**
         * Format an array of color objects for ckeditor
         * @param {Array} values
         * @returns {string}
         */
        const getColors = function (values) {
            const colors = [];
            for (let i = 0; i < values.length; i++) {
                const val = values[i];
                if (val.label && val.css) {
                    // Check if valid color format
                    const css = val.css.match(/^(#[a-f0-9]{3}[a-f0-9]{3}?|rgba?\([0-9, ]+\)|hsla?\([0-9,.% ]+\)) *;?$/i);

                    // If invalid, skip
                    if (!css) {
                        continue;
                    }

                    colors.push({color: css[0], label: val.label});
                }
            }
            return colors;
        };

        // Text color chooser
        if (font.color) {
            colors.push('fontColor');
            config.plugins.push(FontColor);

            if (font.color instanceof Array) {
                config.fontColor = { colors: getColors(font.color) };
            }
        }

        // Text background color chooser
        if (font.background) {
            colors.push('fontBackgroundColor');
            config.plugins.push(FontBackgroundColor);

            if (font.background instanceof Array) {
                config.fontBackgroundColor = { colors: getColors(font.color) };
            }
        }

        // Add the text styling options
        if (styles.length) {
            config.toolbar.items.push(...styles);
        }
        if (colors.length) {
            config.toolbar.items.push(...colors);
        }
    }

    if (inButtons('mathtype')) {
        config.toolbar.items.push('|', 'MathType', 'ChemType');
        config.plugins.push(MathType);
        config.mathTypeParameters = {
            editorParameters: {
                language: 'en',
            },
        };
    }

    if (enterMode === 'p') {
        tags.add('p');
    }
    else {
        tags.add('div');

        // Without this, empty divs get deleted on init of cke
        config.plugins.push(GeneralHtmlSupport);
        config.htmlSupport = {
            allow: [{
                name: 'div',
                attributes: true,
                classes: true,
                styles: true
            }]
        };
    }

    return config;
};
