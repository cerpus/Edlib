import { messagesNnNo as AudioBrowser } from '../components/AudioBrowser/language';
import { messagesNnNo as LicenseChooser } from '../components/LicenseChooser';
import { messagesNnNo as LicenseIcon } from '../components/LicenseIcon';
import { messagesNnNo as LicenseText } from '../components/LicenseText';
import { messagesNnNo as Owner } from '../components/Owner';
import { messagesNnNo as QuestionContentType } from '../components/QuestionContentType';
import { messagesNnNo as TagsManager } from '../components/TagsManager';
import { messagesNnNo as VideoBrowser } from '../components/VideoBrowser/language';
import { messagesNnNo as EmbedContentType } from '../components/EmbedContentType';
import { messagesNnNo as H5pEditor } from '../components/H5pEditor';
import { messagesNnNo as FileUploadProgress } from '../components/FileUploadProgress';
import { messagesNnNo as Sidebar } from '../components/Sidebar';
import { messagesNnNo as Article } from '../components/Article';

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
