import styled from 'styled-components';

export default styled.div`
    background-color: ${(props) => props.theme.colors.tertiary};
    position: absolute;
    left: 50%;
    bottom: -7px;
    z-index: 0;
    transform: rotate(-45deg);
    width: 14px;
    height: 14px;
    display: flex;
`;
