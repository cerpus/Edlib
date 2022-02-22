import React from 'react';
import styled from 'styled-components';
import { Close } from '@material-ui/icons';

const Wrapper = styled.div`
    position: relative;
    display: flex;
    border-bottom: 1px solid #83df66;
    padding: 10px;
    flex-direction: row-reverse;
    justify-content: space-between;
`;

const Content = styled.div`
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
`;

const CloseWrapper = styled.div`
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
`;

export default ({ children, onClose = () => {} }) => {
    return (
        <Wrapper>
            <CloseWrapper onClick={() => onClose()}>
                <Close />
            </CloseWrapper>
            <Content>
                <div>{children}</div>
            </Content>
        </Wrapper>
    );
};
