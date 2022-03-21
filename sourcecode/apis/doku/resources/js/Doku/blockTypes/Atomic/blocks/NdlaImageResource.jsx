import React from 'react';
import AlignmentWrapper from '../../../containers/AlignmentWrapper/AlignmentWrapper';
import styled from 'styled-components';
import useFetch from '../../../hooks/useFetch';

const ImageWrapper = styled.div`
    img {
        display: block;
        z-index: 1;
        max-width: 100%;
    }

    .meta {
        padding: 13px 26px;
        background-color: #f8f8f8;
    }
`;

const NdlaImageResource = ({ data, isEditing, onUpdate }) => {
    const { loading, error, response } = useFetch(data.url, 'GET');

    const imageInfo = response && {
        url: response.imageUrl,
        creators: response.copyright.creators,
    };

    return (
        <AlignmentWrapper
            block={data.block}
            size={data.size}
            align={data.align}
            isEditing={isEditing}
            onUpdate={onUpdate}
        >
            {imageInfo && (
                <ImageWrapper>
                    <img src={imageInfo.url} alt="" />
                    <figcaption className="meta">
                        {imageInfo.creators.map((creator) => (
                            <span key={creator.name}>{creator.name}</span>
                        ))}
                    </figcaption>
                </ImageWrapper>
            )}
        </AlignmentWrapper>
    );
};

export default NdlaImageResource;
