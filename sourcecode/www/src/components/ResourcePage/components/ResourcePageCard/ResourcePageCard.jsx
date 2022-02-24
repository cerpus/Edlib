import React from 'react';
import styled from 'styled-components';
import { Whatshot } from '@mui/icons-material';

const Wrapper = styled.div`
    background-color: white;
    border-radius: 2px;
    box-shadow: 0 0 6px 5px rgba(0, 0, 0, 0.18);
    cursor: pointer;

    &:hover {
        background-color: #ededed;
    }
`;

const HeaderMeta = styled.div`
    display: flex;
    justify-content: space-between;
    font-size: 0.7em;
`;

const Image = styled.div`
    height: 80px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center center;
    margin: 10px 0;
`;

const MetaInfo = styled.div`
    font-size: 0.8em;
    font-weight: lighter;
    margin: 10px 0;
`;

const Footer = styled.div`
    background-color: #ebebeb;
    display: flex;
    font-size: 0.7em;
    padding: 5px;

    & > div {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    & > div:first-child {
        margin-right: 10px;
    }
`;

const ResourcePageCard = ({ resource, onClick }) => {
    return (
        <Wrapper onClick={onClick}>
            <div style={{ padding: 5 }}>
                <HeaderMeta>
                    <div>Interactive video</div>
                    <div>Gratis</div>
                </HeaderMeta>
                <Image style={{ backgroundImage: `url(${resource.image})` }} />
                <div>
                    <strong>{resource.title}</strong>
                </div>
                <MetaInfo>
                    <div>Forfatter: {resource.author}</div>
                    <div>Utgiver: {resource.publisher}</div>
                    <div>Lisens: {resource.license}</div>
                </MetaInfo>
            </div>
            <Footer>
                <div>
                    <Whatshot style={{ fontSize: 18 }} />
                </div>
                <div>Mange gjennomføringer</div>
            </Footer>
        </Wrapper>
    );
};

export default ResourcePageCard;
