import React from 'react';
import apiConfig from '../config/api.js';
import { useTokenContext } from '../contexts/token.js';

const ContentAuthor = () => {
    const { jwt } = useTokenContext();
    return (
        <iframe
            src={
                apiConfig.contentauthorUrl + '/sso-edlib-admin?jwt=' + jwt.value
            }
            style={{
                width: '100%',
                border: 0,
                height: '100%',
            }}
        />
    );
};

export default ContentAuthor;
