import React from 'react';
import PropTypes from 'prop-types';

const LinkMetadata = ({
                          image,
                          title,
                          description,
                          validUrl,
                          url,
                          loading,
                          providerName,
                          providerUrl,
                          embedcode,
                          invalidUrlTranslation
                      }) => {
    return (
        <div id="externalLinkContainer" className={(url.length > 0 ? 'visible' : 'hide')}>
            {loading !== false && (
                <div className="fa fa-spin fa-spinner fa-4x"></div>
            )}
            {url.length > 0 && loading === false && validUrl !== true && (
                <div className="invalidUrl">{invalidUrlTranslation}</div>
            )}
            {url.length > 0 && loading === false && validUrl === true && (
                <div>
                    <div className="linkImageContainer">
                        <img className="linkImage" src={image}></img>
                    </div>
                    <div className="linkTextContainer">
                        <h4>{title}</h4>
                        <p>{description}</p>
                        <div className="provider">{providerName} <span>({providerUrl})</span></div>
                    </div>
                </div>
            )}
        </div>
    );
};

LinkMetadata.PropTypes = {
    image: PropTypes.string,
    title: PropTypes.string,
    description: PropTypes.string,
    validUrl: PropTypes.bool,
    url: PropTypes.string,
    loading: PropTypes.bool,
    providerName: PropTypes.string,
    providerUrk: PropTypes.string,
    embedcode: PropTypes.string,
    invalidUrlTransation: PropTypes.string,
};

export default LinkMetadata;