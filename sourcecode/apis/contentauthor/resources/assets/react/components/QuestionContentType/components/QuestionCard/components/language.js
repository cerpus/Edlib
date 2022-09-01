import {
    messagesEnGb as AnswerMessagesEnGb,
    messagesNbNo as AnswerMessagesNbNo,
    messagesNnNo as AnswerMessagesNnNo,
} from './Answer/language';
import {
    messagesEnGb as AnswerListMessagesEnGb,
    messagesNbNo as AnswerListMessagesNbNo,
    messagesNnNo as AnswerListMessagesNnNo,
} from './AnswerList/language';

const messagesEnGb = Object.assign({}, AnswerMessagesEnGb, AnswerListMessagesEnGb);
const messagesNbNo = Object.assign({}, AnswerMessagesNbNo, AnswerListMessagesNbNo);
const messagesNnNo = Object.assign({}, AnswerMessagesNnNo, AnswerListMessagesNnNo);

export {
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
