import React, { Component } from 'react';
import PropTypes from 'prop-types';
import MillionaireLayout from './MillionaireLayout';
import { Card, rerenderMathJax } from '../utils';
import { FormattedMessage, injectIntl } from 'react-intl';
import { default as Helper } from './MillionaireHelper';
import { default as Validator } from './MillionaireValidator';

export default MillionaireLayout;

class MillionaireContainer extends Component {
    static defaultProps = {
        cards: [],
        tags: [],
        title: null,
        editMode: false,
        isLockedPresentation: false,
    };

    static propTypes = {
        cards: PropTypes.arrayOf(PropTypes.instanceOf(Card)),
        handleDeleteCard: PropTypes.func,
        onChange: PropTypes.func,
        onReturnToOriginal: PropTypes.func,
        onBulkChange: PropTypes.func,
        onToggleDialog: PropTypes.func,
        onSave: PropTypes.func,
        tags: PropTypes.array,
        title: PropTypes.string,
        editMode: PropTypes.bool,
        handleDragEnd: PropTypes.func,
        isLockedPresentation: PropTypes.bool,
    };

    /**
     * @type {Helper}
     */
    helper;

    state = {
        additionalQuestions: [],
        additionalAnswers: [],
        isProcessing: false,
        infoText: '',
        requirementsAreMet: false,
    };

    constructor(props) {
        super(props);

        this.handleOnGenerate = this.handleOnGenerate.bind(this);
        this.handleProcessing = this.handleProcessing.bind(this);
        this.checkRequirements = this.checkRequirements.bind(this);

        this.helper = new Helper(props.intl.formatMessage);
    }

    componentDidMount() {
        if (this.props.editMode === true) {
            return;
        }

        this.checkRequirements();
        this.makeCardsCompatible();
        this.props.onToggleDialog();
        rerenderMathJax();
    }

    checkRequirements() {
        const validator = new Validator(this.props.cards);
        const errors = [];
        const warnings = [];

        if (!validator.isValid) {
            if (validator.hasTooManyQuestions) {
                errors.push(
                    <li key="TMQ">
                        <FormattedMessage id="MILLIONAIRE.TOO_MANY_QUESTIONS" />
                    </li>
                );
            }
            if (validator.hasTooFewQuestions) {
                errors.push(
                    <li key="TFQ">
                        <FormattedMessage id="MILLIONAIRE.TOO_FEW_QUESTIONS" />
                    </li>
                );
            }

            if (validator.hasTooManyCorrects) {
                errors.push(
                    <li key="TMCA">
                        <FormattedMessage
                            id="MILLIONAIRE.TOO_MANY_CORRECT_ALTERNATIVES"
                            values={{
                                'questionList': validator.cardsWithTooManyCorrects.join(', '),
                            }}
                        />
                    </li>
                );
            }

            if (validator.hasTooFewCorrects) {
                errors.push(
                    <li key="TFCA">
                        <FormattedMessage
                            id="MILLIONAIRE.TOO_FEW_CORRECT_ALTERNATIVES"
                            values={{
                                'questionList': validator.cardsWithTooFewCorrects.join(', '),
                            }}
                        />
                    </li>
                );
            }

            if (validator.hasTooManyIncorrects) {
                errors.push(
                    <li key="TMWA">
                        <FormattedMessage
                            id="MILLIONAIRE.TOO_MANY_WRONG_ALTERNATIVES"
                            values={{
                                'questionList': validator.cardsWithTooManyIncorrects.join(', '),
                            }}
                        />
                    </li>
                );
            }

            if (validator.hasTooFewIncorrects) {
                errors.push(
                    <li key="TFWA">
                        <FormattedMessage
                            id="MILLIONAIRE.TOO_FEW_WRONG_ALTERNATIVES"
                            values={{
                                'questionList': validator.cardsWithTooFewIncorrects.join(', '),
                            }}
                        />
                    </li>
                );
            }

            if (validator.hasMissingText) {
                errors.push(
                    <li key="MT">
                        <FormattedMessage
                            id="MILLIONAIRE.HAVE_MISSING_TEXT"
                            values={{
                                'questionList': validator.cardsWithMissingText.join(', '),
                            }}
                        />
                    </li>
                );
            }
        }

        if (validator.hasLongText) {
            warnings.push(
                <li key="TLW">
                    <FormattedMessage
                        id="MILLIONAIRE.TEXT_LENGTH_WARNING"
                        values={{
                            'questionList': validator.cardsWithLongTexts.join(', '),
                        }}
                    />
                </li>
            );
        }

        if (errors.length > 0) {
            errors.unshift(
                <FormattedMessage
                    key="RI"
                    id="MILLIONAIRE.REQUIREMENT_INFO"
                    values={{
                        'questionCount': Validator.REQUIRED_NUM_QUESTIONS,
                        'altCount': Validator.REQUIRED_NUM_CORRECT_ALTERNATIVES + Validator.REQUIRED_NUM_INCORRECT_ALTERNATIVES,
                    }}
                    tagName="div"
                />
            );
        }

        if (warnings.length > 0) {
            warnings.unshift(
                <FormattedMessage
                    key="WI"
                    id="MILLIONAIRE.WARNING_INFO"
                    tagName="div"
                />
            );
        }

        if (errors.length > 0 || warnings.length > 0) {
            this.setState({
                infoText: [].concat(errors, warnings),
                requirementsAreMet: errors.length === 0,
            });
        } else {
            this.setState({
                infoText: <FormattedMessage id="MILLIONAIRE.ALL_SET_AND_READY_TO_GO" />,
                requirementsAreMet: true,
            });
        }
    }

    handleProcessing(isProcessing) {
        this.setState({
            isProcessing: isProcessing,
        });
    }

    makeCardsCompatible() {
        let cards = this.helper.makeCardsCompatible(this.props.cards);
        cards = this.helper.addMissingCards(cards);
        this.props.onBulkChange({ cards: cards });
    }

    handleOnGenerate() {
        if (this.props.onSave) {
            this.props.onSave();
        }
    }

    render() {
        const cards = this.helper.updateUserCanRemove(this.props.cards);

        return (
            <MillionaireLayout
                cards={cards}
                onDeleteCard={this.props.handleDeleteCard}
                onChange={this.props.onChange}
                iconUrl="/graphical/MillionaireIcon.png"
                onReturnToOriginal={this.props.onReturnToOriginal}
                onGenerate={this.state.requirementsAreMet ? this.handleOnGenerate : this.checkRequirements}
                generateButtonText={this.state.requirementsAreMet ? undefined : <FormattedMessage id="MILLIONAIRE.RECHECK_REQUIREMENTS" />}
                processingForm={this.state.isProcessing}
                onChangeProcessing={this.handleProcessing}
                infoText={this.state.infoText}
                editMode={this.props.editMode}
                handleDragEnd={this.props.handleDragEnd}
                isLockedPresentation={this.props.isLockedPresentation}
                questionEditorConfig={{
                    warningAtLength: Validator.RECOMMENDED_MAX_QUESTION_TEXT_LENGTH,
                    warningMessage:
                        <div className="editorWarning">
                            <FormattedMessage
                                id="MILLIONAIRE.EDITOR_LENGTH_WARNING"
                                values={{
                                    charCount: Validator.RECOMMENDED_MAX_QUESTION_TEXT_LENGTH,
                                }}
                            />
                        </div>,
                }}
                answerEditorConfig={{
                    warningAtLength: Validator.RECOMMENDED_MAX_ALTERNATIVE_TEXT_LENGTH,
                    warningMessage:
                        <div className="editorWarning">
                            <FormattedMessage
                                id="MILLIONAIRE.EDITOR_LENGTH_WARNING"
                                values={{
                                    charCount: Validator.RECOMMENDED_MAX_ALTERNATIVE_TEXT_LENGTH,
                                }}
                            />
                        </div>,
                }}
            />);
    }
}

MillionaireContainer = injectIntl(MillionaireContainer);
export { MillionaireContainer };
