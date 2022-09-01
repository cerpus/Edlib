import {
    messagesEnGb as PresentationEnGb,
    messagesNbNo as PresentationNbNo,
    messagesNnNo as PresentationNnNo,
} from './components/Presentation/language';

import {
    messagesEnGb as QuestionCardEnGb,
    messagesNbNo as QuestionCardNbNo,
    messagesNnNo as QuestionCardNnNo,
} from './components/QuestionCard/language';

import {
    messagesEnGb as QuestionContainerEnGb,
    messagesNbNo as QuestionContainerNbNo,
    messagesNnNo as QuestionContainerNnNo,
} from './components/QuestionContainer/language';

import {
    messagesEnGb as QuestionBankBrowserEnGb,
    messagesNbNo as QuestionBankBrowserNbNo,
    messagesNnNo as QuestionBankBrowserNnNo,
} from './components/QuestionBankBrowser/language';

import {
    messagesEnGb as H5PQUIZEnGb,
    messagesNbNo as H5PQUIZNbNo,
    messagesNnNo as H5PQUIZNnNo,
} from './components/H5PQuiz/language';

import {
    messagesEnGb as MillionaireEnGb,
    messagesNbNo as MillionaireNbNo,
    messagesNnNo as MillionaireNnNo,
} from './components/Millionaire/language';

const messagesEnGb = Object.assign({}, PresentationEnGb, QuestionCardEnGb, QuestionContainerEnGb, QuestionBankBrowserEnGb, H5PQUIZEnGb, MillionaireEnGb);
const messagesNbNo = Object.assign({}, PresentationNbNo, QuestionCardNbNo, QuestionContainerNbNo, QuestionBankBrowserNbNo, H5PQUIZNbNo, MillionaireNbNo);
const messagesNnNo = Object.assign({}, PresentationNnNo, QuestionCardNnNo, QuestionContainerNnNo, QuestionBankBrowserNnNo, H5PQUIZNnNo, MillionaireNnNo);

export {
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
