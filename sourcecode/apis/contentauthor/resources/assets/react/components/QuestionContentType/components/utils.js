
export function uniqueId() {
    return 'id-' + Math.random().toString(36).substr(2, 16);
}

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

export function Card() {
    this.id = uniqueId();
    this.order = 0;
    this.canDelete = false;
    this.question = new Question();
    this.answers = [];
    this.externalId = null;
    this.selected = false;
    this.readonly = false;
    this.useImage = true;
    this.canAddAnswer = true;

    this.clone = function () {
        const card = Object.assign(new Card(), this);
        card.question = card.question.clone();
        card.answers = card.answers.map(answer => answer.clone());
        return card;
    };
}

export function Question() {
    this.text = '';
    this.image = null;
    this.richText = true;
    this.readyForSubmit = true;

    this.clone = function () {
        return Object.assign(new Question(), this);
    };
}

export function Image() {
    this.id = null;
    this.url = null;
}

export function rerenderMathJax() {
    if (typeof MathJax !== 'undefined') {
        MathJax.Hub.Queue(['Typeset', MathJax.Hub]);
    }
}
