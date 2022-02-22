import React from 'react';
import Custom from './Custom';

const LeftAlignImage = ({ ...otherProps }) => {
    return (
        <Custom {...otherProps} viewBox="0 0 20.2 17.01">
            <g>
                <rect x="10.73" y="11.34" width="9.47" height="1.89" />
                <g>
                    <path
                        d="M9.4,12.26V4.84c0-0.58-0.47-1.06-1.04-1.06H1.04C0.47,3.78,0,4.26,0,4.84v7.42c0,0.58,0.47,1.06,1.04,1.06h7.31
			C8.93,13.32,9.4,12.85,9.4,12.26z M2.87,9.35l1.31,1.6l1.83-2.39l2.35,3.18H1.04L2.87,9.35z"
                    />
                    <rect x="3.16" y="15.12" width="17.05" height="1.89" />
                    <rect x="3.16" width="17.05" height="1.89" />
                    <rect x="10.73" y="3.78" width="9.47" height="1.89" />
                </g>
                <rect x="10.73" y="7.56" width="9.47" height="1.89" />
            </g>
        </Custom>
    );
};

export default LeftAlignImage;
