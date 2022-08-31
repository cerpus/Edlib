import {
    messagesEnGb as componentsMessagesEn,
    messagesNbNo as componentsMessagesNb,
    messagesNnNo as componentsMessagesNn,
} from './components';

import { messages as messagesEnSidebar } from './language/en-gb';
import { messages as messagesNbSidebar } from './language/nb-no';
import { messages as messagesNnSidebar } from './language/nn-no';

const messagesEnGb = Object.assign({}, componentsMessagesEn, messagesEnSidebar);
const messagesNbNo = Object.assign({}, componentsMessagesNb, messagesNbSidebar);
const messagesNnNo = Object.assign({}, componentsMessagesNn, messagesNnSidebar);

export {
    messagesNbNo,
    messagesEnGb,
    messagesNnNo,
};

export { default } from './Sidebar';
export * from './components';

