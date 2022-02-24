import React from 'react';
import styled from 'styled-components';
import { ArrowBack } from '@mui/icons-material';

const Wrapper = styled.div`
    position: relative;
    display: flex;
    border-bottom: 1px solid #83df66;
    padding: 10px;
`;

const Content = styled.div`
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
`;

const CloseWrapper = styled.div`
    cursor: pointer;
    padding-right: 10px;
    display: flex;
    flex-direction: column;
    justify-content: center;
`;

export default ({ children, onClose = () => {} }) => {
    return (
        <Wrapper>
            <CloseWrapper onClick={() => onClose()}>
                <ArrowBack />
            </CloseWrapper>
            <Content>
                <div>{children}</div>
            </Content>
        </Wrapper>
    );
};
