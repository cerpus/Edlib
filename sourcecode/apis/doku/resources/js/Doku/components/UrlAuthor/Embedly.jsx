import React from 'react';
import styled from 'styled-components';

const StyledEmbedly = styled.div`
    text-align: center;
    > iframe {
        max-width: 100%;
    }
`;

const Embedly = ({ data }) => (
    <StyledEmbedly dangerouslySetInnerHTML={{ __html: data.html }} />
);

export default Embedly;
