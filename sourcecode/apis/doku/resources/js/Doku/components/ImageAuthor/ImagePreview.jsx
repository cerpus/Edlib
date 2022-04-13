import React, { useEffect, useState } from 'react';
import styled from 'styled-components';
import useTranslation from '../../hooks/useTranslation';

const ImageWrapper = styled.div`
    figcaption {
        color: #ffffff;
        background-color: #1e1e1e;
        padding: 5px 10px 5px 50px;
    }

    .caption {
    }

    .caption_aria {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        border: 0;
        overflow: hidden;
        clip: rect(0 0 0 0);
    }

    .photographer {}
    .photographer::before {
        content: ' ';
    }
`;

const Img = styled.img`
    display: block;
    margin-left: auto;
    margin-right: auto;
    max-width: 100%;
    height: 100%;
    width: auto;
`;

export default ({ file, metadata = {}, afterLoad }) => {
    const [imgSource, setImgSource] = useState('');
    const [imgSize, setImgSize] = useState({height: 100, width: 100});
    const { t } = useTranslation();

    useEffect(() => {
        if (file) {
            setImgSource(URL.createObjectURL(file));
        }
    }, [file])

    return (
        <ImageWrapper>
            <Img
                src={imgSource}
                onLoad={(e) => {
                    URL.revokeObjectURL(imgSource);
                    const sizes = {
                        width: e.currentTarget.naturalWidth,
                        height: e.currentTarget.naturalHeight,
                    };
                    setImgSize(sizes);
                    if (typeof afterLoad === 'function') {
                        afterLoad(sizes);
                    }
                }}
                height={imgSize.height}
                width={imgSize.width}
                alt={metadata?.altText ?? file.name}
            />
            {metadata && (metadata.caption || metadata.photographer ) && (
                <figcaption>
                    {metadata.caption && (
                        <>
                            <span className="caption" aria-hidden={true}>
                                {metadata.caption}
                            </span>
                            <span className="caption_aria">
                                {t('Caption')}: {metadata.caption}
                            </span>
                        </>
                    )}
                    {metadata.photographer && (
                        <span className="photographer">
                            {t('Photo')}{': '}
                            {metadata.photographer}
                        </span>
                    )}
                </figcaption>
            )}
        </ImageWrapper>
    );
};
