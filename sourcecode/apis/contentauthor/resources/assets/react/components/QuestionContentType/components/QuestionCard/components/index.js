import AnswerLayout, {
    messagesEnGb as AnswerMessagesEnGb,
    messagesNbNo as AnswerMessagesNbNo,
    messagesNnNo as AnswerMessagesNnNo,
} from './Answer';

import AnswerListLayout, {
    messagesEnGb as AnswerListMessagesEnGb,
    messagesNbNo as AnswerListMessagesNbNo,
    messagesNnNo as AnswerListMessagesNnNo,
} from './AnswerList';

import ImageLayout from './Image';

const messagesEnGb = Object.assign({}, AnswerMessagesEnGb, AnswerListMessagesEnGb);
const messagesNbNo = Object.assign({}, AnswerMessagesNbNo, AnswerListMessagesNbNo);
const messagesNnNo = Object.assign({}, AnswerMessagesNnNo, AnswerListMessagesNnNo);

export { default as AddAnswer } from './AddAnswer';
export { default as QuestionLayout, Question } from './Question';
export { default as Toggle } from './Toggle';
export { ImageContainer } from './Image';

export {
    AnswerLayout,
    AnswerListLayout,
    ImageLayout,
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
