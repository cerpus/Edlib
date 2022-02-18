import React from 'react';
import styled from 'styled-components';

const Wrapper = styled.div`
    margin-bottom: 20px;
`;

const ResourcePageFilterGroup = ({ title, children }) => {
    return (
        <Wrapper>
            {title && (
                <div>
                    <strong>{title}</strong>
                </div>
            )}
            <div>{children}</div>
        </Wrapper>
    );
};

export default ResourcePageFilterGroup;
