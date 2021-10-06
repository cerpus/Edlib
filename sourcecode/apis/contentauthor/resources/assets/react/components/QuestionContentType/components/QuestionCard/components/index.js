import AnswerLayout, { messagesEnGb as AnswerMessagesEnGb, messagesNbNo as AnswerMessagesNbNo } from './Answer';
import AnswerListLayout, { messagesEnGb as AnswerListMessagesEnGb, messagesNbNo as AnswerListMessagesNbNo } from './AnswerList';
import ImageLayout from './Image';

const messagesEnGb = Object.assign({}, AnswerMessagesEnGb, AnswerListMessagesEnGb);
const messagesNbNo = Object.assign({}, AnswerMessagesNbNo, AnswerListMessagesNbNo);

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
};
