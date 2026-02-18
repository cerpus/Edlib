import { VideoBrowser } from '../js/ndla.js';

window.originalVideoWidget = window.H5PEditor.widgets.video;
window.H5PEditor.widgets.video = VideoBrowser;
