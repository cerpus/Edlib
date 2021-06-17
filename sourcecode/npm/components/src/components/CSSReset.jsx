import React from 'react';
import { createGlobalStyle } from 'styled-components';
import { Helmet } from 'react-helmet';
import { Box } from '@material-ui/core';

const GlobalStyle = createGlobalStyle`
  html {
    font-size: ${(props) => props.theme.fontSize}px;
  }

  body .edlib-components {
    * {
      box-sizing: border-box;
    }

    code {
      font-family: source-code-pro, Menlo, Monaco, Consolas, 'Courier New',
      monospace;
    }

    label {
      color: inherit;
    }

    strong {
      font-weight: bold;
    }
  }
`;

const CssReset = ({ children }) => {
    return (
        <Box
            fontFamily="fontFamily"
            className="edlib-components"
            style={{ height: '100%' }}
        >
            <Helmet>
                <link
                    rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
                />
            </Helmet>
            <GlobalStyle />
            {children}
        </Box>
    );
};

export default CssReset;
