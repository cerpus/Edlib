import React from 'react';
import styled from 'styled-components';
import Resources from './components/Resources';
import useTranslation from '../../hooks/useTranslation';
import SearchField from './components/SearchField';
import {
    FromSideModal,
    FromSideModalHeader,
} from '../../components/FromSideModal';
import ResourcePreview from './components/ResourcePreview';
import useResourcesFilters from '../../hooks/useResourcesFilters';
import { Spinner } from '@cerpus/ui';
import { Close } from '@material-ui/icons';
import useSize from '../../hooks/useSize';
import useGetResourcesV2 from '../../hooks/requests/useGetResourcesV2';
import DragIcon from './components/DragIcon';
import Help from './components/Help';
import ExportWrapper from '../../components/ExportWrapper';

const Wrapper = styled.div`
    position: relative;
    font-size: 16px;
`;

const StyledRecommendationEngine = styled.div`
    background-color: white;
    max-height: 100%;
    padding: 15px;
    border: ${(props) => props.theme.border};
    box-shadow: 0 0 6px 5px rgba(0, 0, 0, 0.18);
    position: relative;

    display: flex;
    flex-direction: column;
`;

const TitleWrapper = styled.div`
    display: flex;
    justify-content: space-between;
`;

const Title = styled.div`
    font-weight: 500;
    font-size: 1.1em;
    margin-bottom: 15px;
`;

const CloseWrapper = styled.div`
    cursor: pointer;
`;

const Content = styled.div`
    flex: 1 1;
    overflow-y: auto;
`;

const RecommendationEngine = ({
    rowWrapper,
    context,
    style,
    onClose,
    showDragIcon = false,
}) => {
    const { t } = useTranslation();
    const [selectedResource, setSelectedResource] = React.useState(false);
    const [minHeight, setMinHeight] = React.useState(200);
    const filters = useResourcesFilters('myContent');
    const ref = React.useRef();
    const { height } = useSize(ref);

    const searchString = React.useMemo(
        () =>
            filters.requestData.searchString &&
            filters.requestData.searchString !== ''
                ? filters.requestData.searchString
                : context && Array.isArray(context.keywords)
                ? context.keywords.join(' ')
                : '',
        [filters.requestData.searchString, context]
    );

    const { error, loading, resources } = useGetResourcesV2(
        React.useMemo(() => ({ searchString }), [searchString]),
        searchString === ''
    );

    React.useEffect(() => {
        if (height > minHeight) {
            setMinHeight(height);
        }
    }, [height, minHeight]);

    return (
        <ExportWrapper>
            <Wrapper ref={ref}>
                {showDragIcon && <DragIcon />}
                <StyledRecommendationEngine
                    style={{
                        ...style,
                        minHeight: height,
                    }}
                >
                    <TitleWrapper>
                        <Title>
                            {t('Foreslått innhold')} (beta) <Help />
                        </Title>
                        {onClose && (
                            <CloseWrapper>
                                <Close onClick={onClose} />
                            </CloseWrapper>
                        )}
                    </TitleWrapper>
                    <p>{t('recommendationEngine.explanation')}</p>
                    <SearchField
                        value={filters.searchInput}
                        onChange={(e) => filters.setSearchInput(e.target.value)}
                    />
                    <Content>
                        {loading && <Spinner />}
                        {error && <div>{t('Noe skjedde')}</div>}
                        {!loading && !error && resources && (
                            <Resources
                                resources={resources}
                                rowWrapper={rowWrapper}
                                setSelectedResource={setSelectedResource}
                            />
                        )}
                    </Content>
                    <FromSideModal
                        isOpen={selectedResource}
                        onClose={() => setSelectedResource(null)}
                        usePortal={true}
                    >
                        {selectedResource && (
                            <div
                                style={{
                                    display: 'flex',
                                    flexDirection: 'column',
                                    height: '100%',
                                }}
                            >
                                <FromSideModalHeader
                                    onClose={() => setSelectedResource(null)}
                                >
                                    {selectedResource.name}
                                </FromSideModalHeader>
                                <ResourcePreview resource={selectedResource} />
                            </div>
                        )}
                    </FromSideModal>
                </StyledRecommendationEngine>
            </Wrapper>
        </ExportWrapper>
    );
};

export default RecommendationEngine;
