import {
    messagesEnGb as QuestionCardComponentMessagesEnGb,
    messagesNbNo as QuestionCardComponentMessagesNbNo,
    messagesNnNo as QuestionCardComponentMessagesNnNo,
} from '../components/language';

import { default as QuestionCardEnGb } from './en-gb';
import { default as QuestionCardNbNo } from './nb-no';
import { default as QuestionCardNnNo } from './nn-no';

const messagesEnGb = Object.assign({}, QuestionCardComponentMessagesEnGb, QuestionCardEnGb);
const messagesNbNo = Object.assign({}, QuestionCardComponentMessagesNbNo, QuestionCardNbNo);
const messagesNnNo = Object.assign({}, QuestionCardComponentMessagesNnNo, QuestionCardNnNo);

export {
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
