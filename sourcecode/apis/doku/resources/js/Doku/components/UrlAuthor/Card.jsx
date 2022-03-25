import React from 'react';
import styled from 'styled-components';

const StyledCard = styled.div`
    border: 1px solid gray;
    cursor: pointer;

    img {
        max-width: 100%;
    }
`;

const ImageWrapper = styled.div`
    text-align: center;
`;

const Card = ({ data }) => (
    <StyledCard>
        {data.img && (
            <ImageWrapper>
                <img src={data.img} alt="" />
            </ImageWrapper>
        )}
        <div style={{ padding: 10 }}>
            <div>
                <strong>{data.title}</strong>
            </div>
            <div>{data.description}</div>
            <div>
                <i>{data.provider.url}</i>
            </div>
        </div>
    </StyledCard>
);

export default Card;
