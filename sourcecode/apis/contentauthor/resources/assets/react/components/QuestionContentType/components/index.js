import {
    messagesEnGb as PresentationEnGb,
    messagesNbNo as PresentationNbNo,
    messagesNnNo as PresentationNnNo,
} from './Presentation';

import {
    messagesEnGb as QuestionCardEnGb,
    messagesNbNo as QuestionCardNbNo,
    messagesNnNo as QuestionCardNnNo,
} from './QuestionCard';

import {
    messagesEnGb as QuestionContainerEnGb,
    messagesNbNo as QuestionContainerNbNo,
    messagesNnNo as QuestionContainerNnNo,
} from './QuestionContainer';

import {
    messagesEnGb as QuestionBankBrowserEnGb,
    messagesNbNo as QuestionBankBrowserNbNo,
    messagesNnNo as QuestionBankBrowserNnNo,
} from './QuestionBankBrowser';

import {
    messagesEnGb as H5PQUIZEnGb,
    messagesNbNo as H5PQUIZNbNo,
    messagesNnNo as H5PQUIZNnNo,
} from './H5PQuiz';

import {
    messagesEnGb as MillionaireEnGb,
    messagesNbNo as MillionaireNbNo,
    messagesNnNo as MillionaireNnNo,
} from './Millionaire';

const messagesEnGb = Object.assign(
    {},
    PresentationEnGb,
    QuestionCardEnGb,
    QuestionContainerEnGb,
    QuestionBankBrowserEnGb,
    H5PQUIZEnGb,
    MillionaireEnGb,
);

const messagesNbNo = Object.assign(
    {},
    PresentationNbNo,
    QuestionCardNbNo,
    QuestionContainerNbNo,
    QuestionBankBrowserNbNo,
    H5PQUIZNbNo,
    MillionaireNbNo,
);

const messagesNnNo = Object.assign(
    {},
    PresentationNnNo,
    QuestionCardNnNo,
    QuestionContainerNnNo,
    QuestionBankBrowserNnNo,
    H5PQUIZNnNo,
    MillionaireNnNo,
);

export { CardContainer as QuestionCard} from './QuestionCard';
export { default as CardLayout } from './QuestionCard';
export {QuestionContainer} from './QuestionContainer';
export { default as H5PQuiz} from './H5PQuiz';
export { default as Millionaire} from './Millionaire';

export {
    messagesEnGb,
    messagesNbNo,
    messagesNnNo,
};
