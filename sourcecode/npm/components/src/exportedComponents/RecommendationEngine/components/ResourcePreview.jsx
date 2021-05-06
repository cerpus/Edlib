import React from 'react';
import { Spinner } from '@cerpus/ui';
import styled from 'styled-components';
import moment from 'moment';
import License from '../../../components/License';
import ResourcePreviewContainer from '../../../containers/ResourcePreview';

const Wrapper = styled.div`
    flex: 1;
    height: 0;
    display: flex;
    justify-content: center;
    width: 100%;
    padding: 20px 0;
`;

const Content = styled.div`
    max-width: 800px;
    width: 100%;
    border: ${(props) => props.theme.border};
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    max-height: 100%;
`;

const ResourceContent = styled.div`
    overflow-y: auto;
`;

const ResourceMeta = styled.div`
    background-color: ${(props) => props.theme.colors.border};
    height: 100px;
    display: flex;
`;

const Meta = styled.div`
    display: flex;
    justify-content: center;
    flex-direction: column;
    padding: 0 30px;

    & > div:first-child {
        color: gray;
        margin-bottom: 10px;
    }
`;

export default ({ resource }) => {
    return (
        <Wrapper>
            <Content>
                <ResourcePreviewContainer resource={resource}>
                    {({ error, loading, frame, license }) => (
                        <>
                            <ResourceContent>
                                {loading && <Spinner />}
                                {error && <div>Noe skjedde</div>}
                                {frame}
                            </ResourceContent>
                            <ResourceMeta>
                                <Meta>
                                    <div>Publiseringsdato</div>
                                    <div>
                                        {moment(resource.createdAt).format(
                                            'D. MMMM YYYY'
                                        )}
                                    </div>
                                </Meta>
                                <Meta>
                                    <div>Sist oppdatert</div>
                                    <div>
                                        {moment(resource.updatedAt).format(
                                            'D. MMMM YYYY'
                                        )}
                                    </div>
                                </Meta>
                                <Meta>
                                    <div>Lisens</div>
                                    <div>
                                        {license ? (
                                            <License license={license} />
                                        ) : (
                                            'Ingen'
                                        )}
                                    </div>
                                </Meta>
                            </ResourceMeta>
                        </>
                    )}
                </ResourcePreviewContainer>
            </Content>
        </Wrapper>
    );
};
