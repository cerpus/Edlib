import './HtmlContainer.css';

import React from 'react';
import PropTypes from 'prop-types';

import { stripHTML } from '../../utils/Helper.js';

/**
 * Output a text string as HTML, or optionally strip HTML tags from the string before displaying.
 * Output will be wrapped in a span-element. If passThrough is set, the value of html is displayed as-is
 * without any modifications or wrapping.
 *
 * @property {node} html                            String or if passThrough anything that can be rendered
 * @property {boolean} [passThrough=false]          Display the contents of 'html' without any modifications
 * @property {boolean} [stripTags=true]             Strip tags from 'html' string
 * @property {boolean} [firstParagraphFix=true]     First paragraph in 'html' will be displayed as inline-block
 * @property {boolean} [compactParagraphs=true]     Paragraphs will have no margins
 * @property {string} [className]                   String with CSS class names that is added to the containing span
 */
function HtmlContainer(props) {
    let value = '';

    if (props.passThrough || React.isValidElement(props.html)) {
        return props.html;
    } else {
        const cssClasses = [];

        if (props.className) {
            cssClasses.push(props.className);
        }

        if (props.stripTags) {
            value = (
                <span className={cssClasses.join(' ')}>
                    {stripHTML(props.html)}
                </span>
            );
        } else {
            if (props.compactParagraphs) {
                cssClasses.push('htmlcontainer-paragraph-margin');
            }
            if (props.firstParagraphFix) {
                cssClasses.push('htmlcontainer-firstparagraph-fix');
            }
            value = (
                <span
                    className={cssClasses.join(' ')}
                    // eslint-disable-next-line react/no-danger
                    dangerouslySetInnerHTML={{ __html: props.html }}
                />
            );
        }
    }
    return value;
}

HtmlContainer.propTypes = {
    html: PropTypes.node,
    passThrough: PropTypes.bool,
    stripTags: PropTypes.bool,
    firstParagraphFix: PropTypes.bool,
    compactParagraphs: PropTypes.bool,
    className: PropTypes.string,
};

HtmlContainer.defaultProps = {
    html: '',
    passThrough: false,
    stripTags: true,
    firstParagraphFix: true,
    compactParagraphs: false,
    className: '',
};

export default HtmlContainer;
