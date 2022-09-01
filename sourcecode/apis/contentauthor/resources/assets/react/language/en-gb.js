import { messagesEnGb as AudioBrowser } from '../components/AudioBrowser/language'; //Special for NDLA
import { messagesEnGb as LicenseChooser } from '../components/LicenseChooser/language';
import { messagesEnGb as LicenseIcon } from '../components/LicenseIcon/language';
import { messagesEnGb as LicenseText } from '../components/LicenseText/language';
import { messagesEnGb as Owner } from '../components/Owner/language';
import { messagesEnGb as QuestionContentType } from '../components/QuestionContentType/language';
import { messagesEnGb as TagsManager } from '../components/TagsManager/language';
import { messagesEnGb as VideoBrowser } from '../components/VideoBrowser/language'; //Special for NDLA
import { messagesEnGb as EmbedContentType } from '../components/EmbedContentType/language';
import { messagesEnGb as H5pEditor } from '../components/H5pEditor/language';
import { messagesEnGb as FileUploadProgress } from '../components/FileUploadProgress/language';
import { messagesEnGb as Sidebar } from '../components/Sidebar/language';
import { messagesEnGb as Article } from '../components/Article/language';

export default {
    locale: 'en-GB',
    messages: {
        ...AudioBrowser,
        ...LicenseChooser,
        ...LicenseIcon,
        ...LicenseText,
        ...Owner,
        ...QuestionContentType,
        ...TagsManager,
        ...VideoBrowser,
        ...EmbedContentType,
        ...H5pEditor,
        ...FileUploadProgress,
        ...Sidebar,
        ...Article,
        unpublished_changes: 'Unpublished changes',
        unpublished_changes_explain:
            'Save and close to add the changes to the published version',
    },
};
