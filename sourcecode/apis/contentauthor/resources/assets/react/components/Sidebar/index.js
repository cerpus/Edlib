import { messagesEnGb as componentsMessagesEn, messagesNbNo as componentsMessagesNb } from './components';

import { messages as messagesEnSidebar } from './language/en-gb';
import { messages as messagesNbSidebar } from './language/nb-no';

const messagesEnGb = Object.assign({}, componentsMessagesEn, messagesEnSidebar);
const messagesNbNo = Object.assign({}, componentsMessagesNb, messagesNbSidebar);

export {
    messagesNbNo,
    messagesEnGb,
};

export { default } from './Sidebar';
export * from './components';

