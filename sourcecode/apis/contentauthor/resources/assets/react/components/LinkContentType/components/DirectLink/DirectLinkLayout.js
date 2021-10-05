import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage, injectIntl } from 'react-intl';
import LinkMetadata from './LinkMetadata';

const DirectLinkLayout = ({
                              url,
                              onChangeUrl,
                              metadata,
                              loading
                          }) => {
    return (
        <div className="linkcontent-editor">
            <div className="linkcontent-content">
                <p className="linkcontent-instruction">
                    <FormattedMessage
                        id="link.instruction"
                        defaultMessage="Paste your direct link here:"
                    />
                </p>
                <div>
                    <label className="required" htmlFor="linkUrl">
                        <FormattedMessage
                            id="link.link-label"
                            defaultMessage="URL"
                        />
                    </label>
                    <input
                        className="form-control"
                        id="linkUrl"
                        name="linkUrl"
                        type="text"
                        value={url}
                        onChange={onChangeUrl}
                    />
                    <p className="linkcontent-description">
                        <FormattedMessage
                            id="link.description"
                            defaultMessage="The url must be accessible from the web"
                        />
                    </p>
                </div>
                <input id="linkType" name="linkType" type="hidden" value="external_link" />
                <input name="linkMetadata" type="hidden" value={JSON.stringify(metadata)} />
                <LinkMetadata
                    url={url}
                    title={metadata.title}
                    image={metadata.image}
                    description={metadata.description}
                    validUrl={metadata.validUrl}
                    loading={loading}
                    providerName={metadata.providerName}
                    providerUrl={metadata.providerUrl}
                    embedcode={metadata.code}
                    invalidUrlTranslation={<FormattedMessage
                        id="link.invalid-url"
                        defaultMessage="Invalid url"
                    />}
                />
            </div>
        </div>
    );
};

DirectLinkLayout.PropTypes = {
    url: PropTypes.string,
    title: PropTypes.string,
    linktext: PropTypes.string,
    onChangeUrl: PropTypes.func,
    onChangeTitle: PropTypes.func,
    onChangeLinkText: PropTypes.func,
    metadata: PropTypes.object,
    loading: PropTypes.bool,
};

export default injectIntl(DirectLinkLayout);