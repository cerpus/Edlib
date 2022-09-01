import {
    messagesEnGb as componentsMessagesEn,
    messagesNbNo as componentsMessagesNb,
    messagesNnNo as componentsMessagesNn,
} from '../components/language';

import { default as messagesEnSidebar } from './en-gb';
import { default as messagesNbSidebar } from './nb-no';
import { default as messagesNnSidebar } from './nn-no';

const messagesEnGb = Object.assign({}, componentsMessagesEn, messagesEnSidebar);
const messagesNbNo = Object.assign({}, componentsMessagesNb, messagesNbSidebar);
const messagesNnNo = Object.assign({}, componentsMessagesNn, messagesNnSidebar);

export {
    messagesNbNo,
    messagesEnGb,
    messagesNnNo,
};
