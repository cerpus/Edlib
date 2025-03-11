import { ImageBrowser } from '../js/ndla.js';

window.originalImageWidget = window.H5PEditor.widgets.image;
window.H5PEditor.widgets.image = ImageBrowser;
