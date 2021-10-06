import React, { Component } from 'react';
import PropTypes from 'prop-types';

import QuestionLayout from './Question';
import { Question as QuestionDataType } from '../../../utils';

class Question extends Component {
    static propTypes = {
        text: PropTypes.string,
        collectQuestion: PropTypes.func,
        placeholder: PropTypes.node,
        image: PropTypes.object,
        readonly: PropTypes.bool,
        useImage: PropTypes.bool,
        richText: PropTypes.bool,
    };

    static defaultProps = {
        text: '',
        collectQuestion: null,
        placeholder: null,
        image: null,
        readonly: false,
        useImage: false,
        richText: false,
    };

    constructor(props) {
        super(props);

        this.handleOnChange = this.handleOnChange.bind(this);
    }

    handleChange(values) {
        this.props.collectQuestion({ question: values }, 'question');
    }

    handleOnChange(value, type, submit) {
        let changes = {};
        if ( value !== null) {
            changes = {
                [type]: value,
            };
        }
        changes.readyForSubmit = typeof submit === 'boolean' ? submit : true;
        const values = Object.assign(new QuestionDataType(), { text: this.props.text, image: this.props.image }, changes);
        this.handleChange(values);
    }

    getChangeHandler() {
        if (this.props.collectQuestion) {
            return this.handleOnChange;
        }

        return null;
    }

    render() {
        return (
            <QuestionLayout
                onChange={this.getChangeHandler()}
                question={this.props.text}
                image={this.props.image}
                useImage={this.props.useImage}
                richText={this.props.richText}
            />
        );
    }
}

export default QuestionLayout;

export {
    Question,
};
