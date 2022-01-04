import React, { Component } from 'react';
import { Debounce } from '../../utils/utils';
import Validator from 'validator';
import Axios from 'axios';
import Card from './components/Card';
import Embed from './components/Embed';
import { FormattedMessage } from 'react-intl';
import PropTypes from 'prop-types';

class EmbedContentTypeContainer extends Component {
    state = {
        link: '',
        loading: false,
        invalidUrl: false,
        data: null,
    };

    static propTypes = {
        link: PropTypes.string,
        onChange: PropTypes.func,
    }

    loadEmbedly = Debounce((link) => {
        if (Validator.isURL(link)) {
            this.setState({
                invalidUrl: false,
                loading: link,
                data: null,
                error: false,
            });

            Axios.get('/v1/embed/embedly?link=' + link)
                .then((response) => {
                    this.setState({
                        data: response.data,
                        loading: false,
                    });
                }).catch((error) => {
                    this.setState({
                        data: null,
                        loading: false,
                        error: error.response.status === 404 ? (<FormattedMessage id="embed.notFound" defaultMessage="Didn't find url." />) : (<FormattedMessage id="embed.error" defaultMessage="Something unexpected happened. Please try again." />),
                    });
                });
        } else {
            this.setState({
                invalidUrl: true,
                error: false,
                loading: false,
                data: null,
            });
        }
    }, 1000, false);

    is(type) {
        return this.state.data && this.state.data.type === type;
    }

    render() {
        return (
            <div>
                <div>
                    <label className="required" htmlFor="linkUrl">
                        <FormattedMessage
                            id="embed.link-label"
                            defaultMessage="URL"
                        />
                    </label>
                    <input
                        className="form-control"
                        name="link"
                        type="text"
                        value={this.props.link}
                        onChange={e => {
                            this.props.onChange(e.target.value);
                            this.loadEmbedly(e.target.value);
                        }}
                    />
                    <p className="embedInputDescription">
                        <FormattedMessage
                            id="embed.description"
                            defaultMessage="The url must be accessible from the web"
                        />
                    </p>
                </div>
                {this.state.loading && <div className="fa fa-spin fa-spinner fa-4x" />}
                {this.state.invalidUrl && <div className="embedError">
                    <FormattedMessage
                        id="embed.invalid-url"
                        defaultMessage="Invalid url"
                    />
                </div>}
                {this.state.error && <div className="embedError">
                    {this.state.error}
                </div>}
                {this.is('card') && <Card card={this.state.data} />}
                {this.is('embed') && <Embed embed={this.state.data} />}
            </div>
        );
    }
}

export default EmbedContentTypeContainer;
