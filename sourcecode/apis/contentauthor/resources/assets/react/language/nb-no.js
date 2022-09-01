import { messagesNbNo as AudioBrowser } from '../components/AudioBrowser/language';
import { messagesNbNo as LicenseChooser } from '../components/LicenseChooser/language';
import { messagesNbNo as LicenseIcon } from '../components/LicenseIcon/language';
import { messagesNbNo as LicenseText } from '../components/LicenseText/language';
import { messagesNbNo as Owner } from '../components/Owner/language';
import { messagesNbNo as QuestionContentType } from '../components/QuestionContentType/language';
import { messagesNbNo as TagsManager } from '../components/TagsManager/language';
import { messagesNbNo as VideoBrowser } from '../components/VideoBrowser/language';
import { messagesNbNo as EmbedContentType } from '../components/EmbedContentType/language';
import { messagesNbNo as H5pEditor } from '../components/H5pEditor/language';
import { messagesNbNo as FileUploadProgress } from '../components/FileUploadProgress/language';
import { messagesNbNo as Sidebar } from '../components/Sidebar/language';
import { messagesNbNo as Article } from '../components/Article/language';

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
