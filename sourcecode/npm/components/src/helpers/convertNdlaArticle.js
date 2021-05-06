import { convertFromHTML } from 'draft-convert';
import { convertToRaw } from 'draft-js';
import atomicTypes from '../config/atomicTypes';

const getValue = (attributes) => (name) =>
    attributes[name] ? attributes[name].value : null;

const blockDetector = (node) => {
    const tagName = node.tagName.toLowerCase();
    const attributes = getValue(node.attributes);

    const checks = {
        box: () => tagName === 'div' && attributes('class') === 'c-bodybox',
        aside: () => tagName === 'aside',
        image: () =>
            tagName === 'embed' && attributes('data-resource') === 'image',
        edlibH5p: () =>
            tagName === 'embed' && attributes('data-resource') === 'external',
        mathML: () => tagName === 'math',
    };

    return {
        ...checks,
        atomic: () =>
            ['box', 'aside', 'image', 'edlibH5p'].some((atomicBlock) =>
                checks[atomicBlock]()
            ),
    };
};

const filterDom = (node) => {
    if (node.nodeName === '#text') return node;

    const isBlock = blockDetector(node);
    const tagName = node.tagName.toLowerCase();
    let mustIgnore = ['section'].indexOf(tagName) !== -1;

    if (tagName === 'div' && !isBlock.box()) {
        mustIgnore = true;
    }

    if (mustIgnore && node.childNodes.length === 0) {
        return null;
    }

    let filteredChildren = [];
    for (const currentNode of node.childNodes) {
        const filteredNode = filterDom(currentNode);

        if (filteredNode) {
            if (filteredNode instanceof NodeList) {
                for (const currentFilteredNode of filteredNode) {
                    filteredChildren.push(currentFilteredNode);
                }
            } else {
                filteredChildren.push(filteredNode);
            }
        }
    }

    node.innerHTML = '';

    filteredChildren.forEach((children) => node.appendChild(children));

    if (mustIgnore) {
        return node.childNodes;
    }

    return node;
};

const parse = (html, addBlankFirst = true) => {
    const parser = new DOMParser();
    const htmlDoc = parser.parseFromString(html, 'text/html');

    return convertFromHTML({
        htmlToEntity: (nodeName, node, createEntity) => {
            if (nodeName === '#text') return;

            const attributes = getValue(node.attributes);
            const isBlock = blockDetector(node);

            if (isBlock.image()) {
                const props = {
                    align: 'left',
                    block: true,
                };
                switch (attributes('data-size')) {
                    case 'small':
                        props.size = 's';
                        props.block = false;
                        break;
                    case 'full':
                        props.size = 'full';
                        break;
                }
                switch (attributes('data-align')) {
                    case 'right':
                        props.align = 'right';
                        break;
                    case 'left':
                        props.align = 'left';
                        break;
                }
                return createEntity('image', 'IMMUTABLE', {
                    type: 'ndla',
                    url: attributes('data-url'),
                    ...props,
                });
            }

            if (isBlock.aside()) {
                return createEntity(atomicTypes.SIDE_NOTE, 'MUTABLE', {
                    editorState: convertToRaw(parse(node.innerHTML, false)),
                });
            }

            if (isBlock.box()) {
                return createEntity(atomicTypes.BOX, 'MUTABLE', {
                    editorState: convertToRaw(parse(node.innerHTML, false)),
                });
            }

            if (isBlock.edlibH5p()) {
                return createEntity(
                    atomicTypes.NDLA_EDLIB_RESOURCE,
                    'MUTABLE',
                    {
                        url: attributes('data-url'),
                    }
                );
            }

            if (isBlock.mathML()) {
                return createEntity(atomicTypes.MATH, 'MUTABLE', {
                    mathML: node.outerHTML,
                });
            }

            return false;
        },
        htmlToBlock: (nodeName, node) => {
            if (nodeName === '#text') return;

            const attributes = getValue(node.attributes);
            const isBlock = blockDetector(node);

            if (isBlock.atomic()) {
                node.setAttribute('data-html', node.innerHTML);
                node.innerHTML = ' ';

                return {
                    type: 'atomic',
                    text: ' ',
                    data: {},
                };
            }

            if (nodeName === 'blockquote') {
                return {
                    type: 'blockquote',
                    data: {},
                };
            }

            if (nodeName === 'h2') {
                return 'header-two';
            }
        },
    })(
        `${addBlankFirst ? '<p> </p>' : ''}${
            filterDom(htmlDoc.getElementsByTagName('body')[0]).innerHTML
        }`,
        {
            flat: false,
        }
    );
};

export default parse;
