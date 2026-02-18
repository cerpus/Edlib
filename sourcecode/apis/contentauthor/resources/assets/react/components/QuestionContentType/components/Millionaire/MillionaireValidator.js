import { Card } from '../utils';

/**
 * Check that the cards, i.e. the questions and answer alternatives, can be used in Millionaire game
 */
export default class MillionaireValidator
{
    static REQUIRED_NUM_QUESTIONS = 15;
    static REQUIRED_NUM_CORRECT_ALTERNATIVES = 1;
    static REQUIRED_NUM_INCORRECT_ALTERNATIVES = 3;
    static RECOMMENDED_MAX_QUESTION_TEXT_LENGTH = 100;
    static RECOMMENDED_MAX_ALTERNATIVE_TEXT_LENGTH = 25;

    #hasTooManyQuestions = false;
    #hasTooFewQuestions = false;

    /** @type number[] */
    #hasTooManyCorrects = [];
    /** @type number[] */
    #hasTooFewCorrects = [];
    /** @type number[] */
    #hasTooManyIncorrects = [];
    /** @type number[] */
    #hasTooFewIncorrects = [];
    /** @type number[] */
    #hasMissingText = [];
    /** @type number[] */
    #mayHaveTooLongText = [];

    /**
     * @param {Card[]} cards
     */
    constructor(cards) {
        this.#hasTooManyQuestions = cards.length > MillionaireValidator.REQUIRED_NUM_QUESTIONS;
        this.#hasTooFewQuestions = cards.length < MillionaireValidator.REQUIRED_NUM_QUESTIONS;

        // Check if correct number of alternatives
        cards.forEach(card => {
            const numCorrect = card.numCorrectAnswers();
            const numIncorrect = card.numIncorrectAnswers();

            if (card.question.text.trim().length === 0 || card.answers.filter(a => a.answerText.trim().length === 0).length > 0) {
                this.#hasMissingText.push(card.order + 1);
            }

            if (card.question.text.trim().length > MillionaireValidator.RECOMMENDED_MAX_QUESTION_TEXT_LENGTH ||
                card.answers.filter(
                    a => a.answerText.trim().length > MillionaireValidator.RECOMMENDED_MAX_ALTERNATIVE_TEXT_LENGTH
                ).length > 0
            ) {
                this.#mayHaveTooLongText.push(card.order + 1);
            }

            if (numCorrect > MillionaireValidator.REQUIRED_NUM_CORRECT_ALTERNATIVES) {
                this.#hasTooManyCorrects.push(card.order + 1);
            }

            if (numCorrect < MillionaireValidator.REQUIRED_NUM_CORRECT_ALTERNATIVES) {
                this.#hasTooFewCorrects.push(card.order + 1);
            }

            if (numIncorrect > MillionaireValidator.REQUIRED_NUM_INCORRECT_ALTERNATIVES) {
                this.#hasTooManyIncorrects.push(card.order + 1);
            }

            if (numIncorrect < MillionaireValidator.REQUIRED_NUM_INCORRECT_ALTERNATIVES) {
                this.#hasTooFewIncorrects.push(card.order + 1);
            }
        });
    }

    /**
     * @return {boolean}
     */
    get isValid() {
        return !this.hasTooFewQuestions &&
            !this.hasTooManyQuestions &&
            this.hasTooFewCorrects &&
            this.hasTooManyCorrects &&
            this.hasTooFewIncorrects &&
            this.hasTooManyIncorrects &&
            this.hasMissingText;
    }

    /**
     * @return {number[]}
     */
    get cardsWithTooManyCorrects() {
        return this.#hasTooManyCorrects;
    }

    /**
     * @return {number[]}
     */
    get cardsWithTooFewCorrects() {
        return this.#hasTooFewCorrects;
    }

    /**
     * @return {number[]}
     */
    get cardsWithTooManyIncorrects() {
        return this.#hasTooManyIncorrects;
    }

    /**
     * @return {number[]}
     */
    get cardsWithTooFewIncorrects() {
        return this.#hasTooFewIncorrects;
    }

    /**
     * @return {number[]}
     */
    get cardsWithMissingText() {
        return this.#hasMissingText;
    }

    /**
     * @return {number[]}
     */
    get cardsWithLongTexts() {
        return this.#mayHaveTooLongText;
    }

    /**
     * @return {boolean}
     */
    get hasTooFewQuestions() {
        return this.#hasTooFewQuestions;
    }

    /**
     * @return {boolean}
     */
    get hasTooManyQuestions() {
        return this.#hasTooManyQuestions;
    }

    get hasTooFewCorrects() {
        return this.#hasTooFewCorrects.length > 0;
    }

    get hasTooManyCorrects() {
        return this.#hasTooManyCorrects.length > 0;
    }

    get hasTooFewIncorrects() {
        return this.#hasTooFewIncorrects.length > 0;
    }

    get hasTooManyIncorrects() {
        return this.#hasTooManyIncorrects.length > 0;
    }

    get hasMissingText() {
        return this.#hasMissingText.length > 0;
    }

    get hasLongText() {
        return this.#mayHaveTooLongText.length > 0;
    }
}
