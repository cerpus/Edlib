import { AudioBrowser } from '../js/ndla.js';

window.originalAudioWidget = window.H5PEditor.widgets.audio;
window.H5PEditor.widgets.audio = AudioBrowser;
