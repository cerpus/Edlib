import { messagesEnGb as AudioBrowser } from '../components/AudioBrowser/language'; //Special for NDLA
import { messagesEnGb as LicenseChooser } from '../components/LicenseChooser';
import { messagesEnGb as LicenseIcon } from '../components/LicenseIcon';
import { messagesEnGb as LicenseText } from '../components/LicenseText';
import { messagesEnGb as Owner } from '../components/Owner';
import { messagesEnGb as QuestionContentType } from '../components/QuestionContentType';
import { messagesEnGb as TagsManager } from '../components/TagsManager';
import { messagesEnGb as VideoBrowser } from '../components/VideoBrowser/language'; //Special for NDLA
import { messagesEnGb as EmbedContentType } from '../components/EmbedContentType';
import { messagesEnGb as H5pEditor } from '../components/H5pEditor';
import { messagesEnGb as FileUploadProgress } from '../components/FileUploadProgress';
import { messagesEnGb as Sidebar } from '../components/Sidebar';
import { messagesEnGb as Article } from '../components/Article';

export default {
    locale: 'en-GB',
    messages: Object.assign(
        {},
        AudioBrowser,
        LicenseChooser,
        LicenseIcon,
        LicenseText,
        Owner,
        QuestionContentType,
        TagsManager,
        VideoBrowser,
        EmbedContentType,
        H5pEditor,
        FileUploadProgress,
        Sidebar,
        Article
    ),
};
