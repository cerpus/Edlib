import React from 'react';
import PropTypes from 'prop-types';

const EDLLIcon = ({className}) => {
    const iconStyle = {
        fill: 'none',
        stroke: '#000000',
        strokeWidth: 1.5,
        strokeMiterlimit: 10,
    };

    return (
        <svg
            version="1.0" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlnsXlink={'http://www.w3.org/1999/xlink'}
            x="0px" y="0px"
            viewBox="0 0 19.5 19.5" contentStyleType="enable-background:new 0 0 19.5 19.5;" xmlSpace={'preserve'}
            className={className}
        >
            <circle style={iconStyle} cx="9.75" cy="9.75" r="9"/>
            <g>
                <g>
                    <g>
                        <path d="M13.68,3.22c-0.87,5.27-5.08,9.12-9.8,8.75c-0.1-0.01-0.21-0.02-0.31-0.04c0.69-0.29,1.37-0.66,2.03-1.1
				c1.29-0.85,2.35-1.88,3.16-2.99c-1.65,0.73-3.64,1.8-5.74,3.12c-0.09-0.52-0.12-1.06-0.08-1.6c0.31-4.03,4.04-7.02,8.33-6.69
				C12.12,2.72,12.93,2.92,13.68,3.22z"/>
                    </g>
                    <g>
                        <path d="M9.2,16.84c-2.57,0.15-4.61-1.56-5.29-4.16c-0.01-0.05,0.93-0.26,1.28-0.24c1.95,0.07,3.73,1.16,4.02,3.21
				C9.27,16.05,9.26,16.45,9.2,16.84z"/>
                    </g>
                </g>
            </g>
        </svg>
    );
};

EDLLIcon.propTypes = {
    className: PropTypes.string,
}

export default EDLLIcon;
