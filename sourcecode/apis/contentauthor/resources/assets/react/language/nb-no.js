import { messagesNbNo as AudioBrowser } from '../components/AudioBrowser/language';
import { messagesNbNo as LicenseChooser } from '../components/LicenseChooser';
import { messagesNbNo as LicenseIcon } from '../components/LicenseIcon';
import { messagesNbNo as LicenseText } from '../components/LicenseText';
import { messagesNbNo as Owner } from '../components/Owner';
import { messagesNbNo as QuestionContentType } from '../components/QuestionContentType';
import { messagesNbNo as TagsManager } from '../components/TagsManager';
import { messagesNbNo as VideoBrowser } from '../components/VideoBrowser/language';
import { messagesNbNo as EmbedContentType } from '../components/EmbedContentType';
import { messagesNbNo as H5pEditor } from '../components/H5pEditor';
import { messagesNbNo as FileUploadProgress } from '../components/FileUploadProgress';
import { messagesNbNo as Sidebar } from '../components/Sidebar';
import { messagesNbNo as Article } from '../components/Article';

export default {
    locale: 'nb-NO',
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
        unpublished_changes: 'Upubliserte endringer',
        unpublished_changes_explain:
            'Lagre og lukk for legge til endringene til den publiserte versjonen',
    },
};
