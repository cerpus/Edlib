import { messagesEnGb as PresentationEnGb, messagesNbNo as PresentationNbNo } from './Presentation';
import { messagesEnGb as QuestionCardEnGb, messagesNbNo as QuestionCardNbNo } from './QuestionCard';
import { messagesEnGb as QuestionContainerEnGb, messagesNbNo as QuestionContainerNbNo } from './QuestionContainer';
import { messagesEnGb as QuestionBankBrowserEnGb, messagesNbNo as QuestionBankBrowserNbNo } from './QuestionBankBrowser';
import { messagesEnGb as H5PQUIZEnGb, messagesNbNo as H5PQUIZNbNo } from './H5PQuiz';
import { messagesEnGb as MillionaireEnGb, messagesNbNo as MillionaireNbNo } from './Millionaire';

const messagesEnGb = Object.assign({}, PresentationEnGb, QuestionCardEnGb, QuestionContainerEnGb, QuestionBankBrowserEnGb, H5PQUIZEnGb, MillionaireEnGb);
const messagesNbNo = Object.assign({}, PresentationNbNo, QuestionCardNbNo, QuestionContainerNbNo, QuestionBankBrowserNbNo, H5PQUIZNbNo, MillionaireNbNo);

export { CardContainer as QuestionCard} from './QuestionCard';
export { default as CardLayout } from './QuestionCard';
export {QuestionContainer} from './QuestionContainer';
export { default as H5PQuiz} from './H5PQuiz';
export { default as Millionaire} from './Millionaire';

export {
    messagesEnGb,
    messagesNbNo,
};
