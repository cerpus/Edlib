/**
 * @return {string}
 */
export function uniqueId() {
    return 'id-' + Math.random().toString(36).substring(2, 18);
}

/**
 * @typedef Answer
 * @property {string} id
 * @property {string} [answerText = '']
 * @property {boolean} [isCorrect = true]
 * @property {boolean} [showToggle = false]
 * @property {boolean} [canDelete = false]
 * @property {Image|null} [image = null]
 * @property {string|null} [title = null]
 * @property {boolean} [readonly = false]
 * @property {string|null} [placeholder = null]
 * @property {string|null} [additionalClass = null]
 * @property {string|null} [externalId = null]
 * @property {boolean} [useImage = false]
 * @property {boolean} [richText = true]
 * @property {boolean} [readyForSubmit = true]
 */
export function Answer() {
    this.id = uniqueId();
    this.answerText = '';
    this.isCorrect = true;
    this.showToggle = false;
    this.canDelete = false;
    this.image = null;
    this.title = null;
    this.readonly = false;
    this.placeholder = null;
    this.additionalClass = null;
    this.externalId = null;
    this.useImage = false;
    this.richText = true;
    this.readyForSubmit = true;

    this.clone = function () {
        return Object.assign(new Answer(), this);
    };
}

/**
 * @typedef Card
 * @property {string} id
 * @property {number} [order = 0]
 * @property {boolean} [canDelete = false]
 * @property {Question} question
 * @property {Answer[]} answers
 * @property {string|number|null} [externalId = null]
 * @property {boolean} [selected = false]
 * @property {boolean} [readonly = false]
 * @property {boolean} [useImage = false]
 * @property {boolean} [canAddAnswer = true]
 */
export function Card() {
    this.id = uniqueId();
    this.order = 0;
    this.canDelete = false;
    this.question = new Question();
    this.answers = [];
    this.externalId = null;
    this.selected = false;
    this.readonly = false;
    this.useImage = false;
    this.canAddAnswer = true;

    this.clone = function () {
        const card = Object.assign(new Card(), this);
        card.question = card.question.clone();
        card.answers = card.answers.map(answer => answer.clone());
        return card;
    };

    this.numAnswers = function() {
        return Array.isArray(this.answers) ? this.answers.length : 0;
    }

    this.numCorrectAnswers = function() {
        if (Array.isArray(this.answers)) {
            return this.answers.filter(answer => answer.isCorrect).length;
        }

        return 0;
    };

    this.numIncorrectAnswers = function() {
        if (Array.isArray(this.answers)) {
            return this.answers.filter(answer => !answer.isCorrect).length;
        }

        return 0;
    }

    /**
     * @param {Answer} answer
     */
    this.addAnswer = function(answer) {
        if (answer instanceof Answer) {
            this.answers = [].concat(this.answers, [answer]);
        }
    }
}

/**
 * @typedef Question
 * @property {string} [text = '']
 * @property {Image|null} [image = null]
 * @property {boolean} [richText = true]
 * @property {boolean} [readyForSubmit = true]
 */
export function Question() {
    this.text = '';
    this.image = null;
    this.richText = true;
    this.readyForSubmit = true;

    this.clone = function () {
        return Object.assign(new Question(), this);
    };
}

/**
 * @typedef Image
 * @property {string|null} [id = null]
 * @property {string|null} [url = null]
 */
export function Image() {
    this.id = null;
    this.url = null;
}

export function rerenderMathJax() {
    if (typeof MathJax !== 'undefined') {
        MathJax.Hub.Queue(['Typeset', MathJax.Hub]);
    }
}
