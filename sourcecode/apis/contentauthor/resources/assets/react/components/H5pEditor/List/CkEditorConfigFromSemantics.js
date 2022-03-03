const baseConfig = {
    extraPlugins: 'autogrow,justify',
    removeButtons: '',
    startupFocus: true,
    height: 80,
    autoGrow_minHeight: 80,
    autoGrow_onStartup: true,
    autoGrow_maxHeight: 500,
    width: 'calc(100%)',
};

const CKEDITOR_ENTERMODE_P = 1;
const CKEDITOR_ENTERMODE_DIV = 3;
const defaultTags = ['strong', 'em', 'del', 'h2', 'h3', 'a', 'ul', 'ol', 'table', 'hr'];
const defaultFontSizes = [8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72];

const convertFontSemantics = (input, propertyName, defaultPropertyName) => {
    const output = {};
    const values = input
        .map(value => {
            if (value.default) {
                output[defaultPropertyName] = value.label;
            }
            return value.label + '/' + value.css;
        })
        .join(';');

    if (values.length > 0) {
        output[propertyName] = values;
    }

    return output;
};

const convertColorSemantics = input => input
    .filter(value => value.label && value.css)
    .map(value => {
        const css = value.css.match(/^#?([a-f0-9]{3}[a-f0-9]{3}?)$/i);
        return css ? value.label + '/' + css[1] : null;
    })
    .filter(value => typeof value === 'string')
    .join(',');

/**
 * Convert the H5P semantics to CKEditor config
 * @see vendor/h5p/h5p-editor/scripts/h5peditor-html.js
 * @see https://h5p.org/semantics
 */
export default ({ font, tags: wantedTags, enterMode }) => {
    const tags = wantedTags ?? defaultTags;
    const config = {
        ...baseConfig,
        toolbar: [
            {
                name: 'document',
                items: ['Source'],
            }
        ],
        enterMode: enterMode === 'p' ? CKEDITOR_ENTERMODE_P : CKEDITOR_ENTERMODE_DIV,
    };
    const items = {
        basicStyles: [],
        color: [],
        formats: [],
        inserts: [],
        links: [],
        paragraph: [],
        styles: [],
    };
    const hasTag = check => check.some(tag => tags.includes(tag));

    if (hasTag(['strong', 'b'])) {items.basicStyles.push('Bold');}
    if (hasTag(['em', 'i'])) {items.basicStyles.push('Italic');}
    if (hasTag(['u'])) {items.basicStyles.push('Underline');}
    if (hasTag(['strike', 'del', 's'])) {items.basicStyles.push('Strike');}
    if (hasTag(['sub'])) {items.basicStyles.push('Subscript');}
    if (hasTag(['sup'])) {items.basicStyles.push('Superscript');}

    if (hasTag(['ul'])) {items.paragraph.push('BulletedList');}
    if (hasTag(['ol'])) {items.paragraph.push('NumberedList');}
    if (hasTag(['blockquote'])) {items.paragraph.push('Blockquote');}

    if (hasTag(['a'])) {
        items.links.push('Link', 'Unlink');
        if (hasTag(['anchor'])) {items.links.push('Anchor');}
    }

    if (hasTag(['table'])) {items.inserts.push('Table');}
    if (hasTag(['hr'])) {items.inserts.push('HorizontalRule');}

    if (hasTag(['h1'])) {items.formats.push('h1');}
    if (hasTag(['h2'])) {items.formats.push('h2');}
    if (hasTag(['h3'])) {items.formats.push('h3');}
    if (hasTag(['h4'])) {items.formats.push('h4');}
    if (hasTag(['h5'])) {items.formats.push('h5');}
    if (hasTag(['h6'])) {items.formats.push('h6');}
    if (hasTag(['address'])) {items.formats.push('address');}
    if (hasTag(['pre'])) {items.formats.push('pre');}
    if (items.formats.length > 0 || hasTag(['p', 'div'])) {
        items.formats.push('p');
        items.styles.push('Format');
        config.format_tags = items.formats.join(';');
        config.enterMode = CKEDITOR_ENTERMODE_P;
    }

    if (font.family) {
        items.styles.push('Font');
        if (Array.isArray(font.family)) {
            Object.assign(config, convertFontSemantics(font.family, 'font_names', 'font_defaultLabel'));
        }
    }

    if (font.size) {
        items.styles.push('FontSize');
        if (Array.isArray(font.size)) {
            Object.assign(config, convertFontSemantics(font.size, 'fontSize_sizes', 'fontSize_defaultLabel'));
        } else {
            config.fontSize_defaultLabel = '100%';
            config.fontSize_sizes = defaultFontSizes
                .map(size => ((size / 16) * 100) + '%/' + (size / 16) + 'em')
                .join(';');
        }
    }

    if (font.color) {items.color.push('TextColor');}
    if (font.background) {items.color.push('BGColor');}

    // Add the toolbar groups that have items
    if (items.basicStyles.length > 0) {
        config.toolbar.push({
            name: 'items.basicStyles',
            items: items.basicStyles.concat(['-', 'RemoveFormat']),
        });
    }

    config.toolbar.push({
        name: 'justify',
        items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight'],
    });

    if (items.paragraph.length > 0) {
        config.toolbar.push(items.paragraph);
    }

    if (items.links.length > 0) {
        config.toolbar.push({
            name: 'links',
            items: items.links,
        });
    }

    if (items.inserts.length > 0) {
        config.toolbar.push({
            name: 'insert',
            items: items.inserts,
        });
    }

    if (items.styles.length > 0) {
        if (items.styles.includes('Font') || items.styles.includes('FontSize')) {
            config.extraPlugins += ',font';
        }
        config.toolbar.push({
            name: 'styles',
            items: items.styles,
        });
    }

    if (items.color.length > 0) {
        config.extraPlugins += ',colorbutton,colordialog';
        if (Array.isArray(font.color) || Array.isArray(font.background)) {
            Object.assign(config, {
                colorButton_colors: convertColorSemantics(Array.isArray(font.color) ? font.color : font.background),
                colorButton_enableMore: false,
            });
        }
        config.toolbar.push({
            name: 'colors',
            items: items.color,
        });
    }

    return config;
};
