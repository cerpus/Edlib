import { Answer, Card } from '../utils';
import { stripHTML } from '../../../../utils/Helper';
import { default as Validator } from './MillionaireValidator';

export default class MillionaireHelper {
    /**
     * @type {function}
     * @return {string}
     */
    formatMessage;

    /**
     * @param {function} formatMessage The formatMessage function from react-intl
     */
    constructor(formatMessage) {
        this.formatMessage = formatMessage;
    }

    /**
     * Add missing cards with question and answer alternaives
     *
     * @param {Card[]} cards
     * @returns {Card[]}
     */
    addMissingCards (cards) {
        while (cards.length < Validator.REQUIRED_NUM_QUESTIONS) {
            const card = this.addMissingAlternatives(this.createCard());
            card.order = cards.length;

            cards = [].concat(cards, [card]);
        }

        return cards;
    }

    /**
     * Add missing answer alternatives
     *
     * @param {Card} card
     * @return {Card}
     */
    addMissingAlternatives (card) {
        if (card.numCorrectAnswers() < Validator.REQUIRED_NUM_CORRECT_ALTERNATIVES) {
            card.addAnswer(this.createAnswer(true));
        }

        while (card.numIncorrectAnswers() < Validator.REQUIRED_NUM_INCORRECT_ALTERNATIVES) {
            card.addAnswer(this.createAnswer(false));
        }

        return card;
    }

    /**
     * Toggle setting on cards and alternatives to allow deleting if too many
     *
     * @param {Card[]} cards
     * @return {Card[]}
     */
    updateUserCanRemove(cards) {
        const canDeleteCards = cards.length > Validator.REQUIRED_NUM_QUESTIONS;

        return cards.map(card => {
            const canDeleteCorrect = card.numCorrectAnswers() > Validator.REQUIRED_NUM_CORRECT_ALTERNATIVES;
            const canDeleteWrong = card.numIncorrectAnswers() > Validator.REQUIRED_NUM_INCORRECT_ALTERNATIVES;

            card.canDelete = canDeleteCards;

            card.answers.map(answer => {
                answer.canDelete = answer.isCorrect ? canDeleteCorrect : canDeleteWrong;
                return answer;
            });

            return card;
        });
    }

    /**
     * Adjust settings on cards and its question and answer alternatives
     *
     * @param {Card[]} cards
     * @return {Card[]}
     */
    makeCardsCompatible(cards) {
        return cards.map((card, index) => {
            card.order = index;
            return this.addMissingAlternatives(this.makeCardCompatible(card));
        });
    }

    /**
     * Adjust settings on the Card and its question and answer alternatives
     *
     * @param {Card} card
     * @return {Card}
     */
    makeCardCompatible(card) {
        card.useImage = false;
        card.canDelete = false;
        card.canAddAnswer = false;

        card.question.image = null;
        card.question.richText = false;
        card.question.text = stripHTML(card.question.text, true);

        card.answers = card.answers.map(this.makeAnswerCompatible);

        return card;
    }

    /**
     * @param {Answer} answer
     * @return {Answer}
     */
    makeAnswerCompatible(answer) {
        answer.image = null;
        answer.useImage = false;
        answer.richText = false;
        answer.answerText = stripHTML(answer.answerText, true);
        answer.showToggle = false;

        return answer;
    }

    /**
     * @return {Card}
     */
    createCard() {
        return this.makeCardCompatible(new Card());
    }

    /**
     * @param {boolean} isCorrect
     * @return {Answer}
     */
    createAnswer(isCorrect) {
        const answer = this.makeAnswerCompatible(new Answer());
        answer.placeholder = this.formatMessage({ id: 'MILLIONAIRE.MISSING_TEXT' });
        answer.title = this.formatMessage({ id: 'MILLIONAIRE.ADDED_ALTERNATIVE' });
        answer.additionalClass = 'H5PQuizAddedAlternative';
        answer.isCorrect = isCorrect;

        return answer;
    }
}
