import React from 'react';
import { createGlobalStyle } from 'styled-components';
import { Helmet } from 'react-helmet';

const GlobalStyle = createGlobalStyle`
  html {
    font-size: ${(props) => props.theme.fontSize}px;
  }

  body .edlib-components {
    font-family: 'Lato', sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;

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
        <div className="edlib-components" style={{ height: '100%' }}>
            <Helmet>
                <link
                    rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
                />
            </Helmet>
            <GlobalStyle />
            {children}
        </div>
    );
};

export default CssReset;
