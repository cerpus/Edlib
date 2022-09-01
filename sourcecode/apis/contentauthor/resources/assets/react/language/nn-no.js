import { messagesNnNo as AudioBrowser } from '../components/AudioBrowser/language';
import { messagesNnNo as LicenseChooser } from '../components/LicenseChooser/language';
import { messagesNnNo as LicenseIcon } from '../components/LicenseIcon/language';
import { messagesNnNo as LicenseText } from '../components/LicenseText/language';
import { messagesNnNo as Owner } from '../components/Owner/language';
import { messagesNnNo as QuestionContentType } from '../components/QuestionContentType/language';
import { messagesNnNo as TagsManager } from '../components/TagsManager/language';
import { messagesNnNo as VideoBrowser } from '../components/VideoBrowser/language';
import { messagesNnNo as EmbedContentType } from '../components/EmbedContentType/language';
import { messagesNnNo as H5pEditor } from '../components/H5pEditor/language';
import { messagesNnNo as FileUploadProgress } from '../components/FileUploadProgress/language';
import { messagesNnNo as Sidebar } from '../components/Sidebar/language';
import { messagesNnNo as Article } from '../components/Article/language';

export default {
    locale: 'nn-NO',
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
