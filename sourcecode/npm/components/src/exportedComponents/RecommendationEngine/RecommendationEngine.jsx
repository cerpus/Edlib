import React from 'react';
import styled from 'styled-components';
import Resources from './components/Resources';
import useTranslation from '../../hooks/useTranslation';
import SearchField from './components/SearchField';
import PaginatedResources from '../../containers/PaginatedResources';
import {
    FromSideModal,
    FromSideModalHeader,
} from '../../components/FromSideModal';
import ResourcePreview from './components/ResourcePreview';
import Filters from './components/Filters';
import useResourcesFilters from '../../hooks/useResourcesFilters';
import { Tune as TuneIcon } from '@material-ui/icons';
import useSize from '../../hooks/useSize';
import { Button } from '@material-ui/core';
import ExportWrapper from '../../components/ExportWrapper';

const StyledRecommendationEngine = styled.div`
    padding: 15px 15px;
    border: ${(props) => props.theme.border};
    box-shadow: 0 0 6px 5px rgba(0, 0, 0, 0.18);
    position: relative;

    display: flex;
    flex-direction: column;
`;

const Title = styled.div`
    font-weight: 500;
    font-size: 1.1em;
    margin-bottom: 15px;
`;

const RecommendationEngine = ({ rowWrapper }) => {
    const { t } = useTranslation();
    const [showFilters, setShowFilters] = React.useState(false);
    const [selectedResource, setSelectedResource] = React.useState(false);
    const [minHeight, setMinHeight] = React.useState(200);
    const filters = useResourcesFilters('myContent');
    const ref = React.useRef();
    const { height } = useSize(ref);

    React.useEffect(() => {
        if (height > minHeight) {
            setMinHeight(height);
        }
    }, [height, minHeight]);

    return (
        <ExportWrapper>
            <StyledRecommendationEngine ref={ref} style={{ minHeight }}>
                <Title>{t('Foreslått innhold')}</Title>
                <p>{t('recommendationEngine.explanation')}</p>
                <SearchField
                    value={filters.searchInput}
                    onChange={(e) => filters.setSearchInput(e.target.value)}
                />
                <PaginatedResources filters={filters}>
                    {({ resources }) => (
                        <Resources
                            resources={resources}
                            rowWrapper={rowWrapper}
                            setSelectedResource={setSelectedResource}
                        />
                    )}
                </PaginatedResources>
                <div>
                    <Button
                        style={{ marginTop: 10 }}
                        color="primary"
                        variant="outlined"
                        onClick={() => setShowFilters(true)}
                        startIcon={<TuneIcon />}
                    >
                        <span style={{ textTransform: 'uppercase' }}>
                            {t('avansert søk')}
                        </span>
                    </Button>
                </div>
                {showFilters && (
                    <Filters
                        filters={filters}
                        onClose={() => setShowFilters(false)}
                    />
                )}
                <FromSideModal
                    isOpen={selectedResource}
                    onClose={() => setSelectedResource(null)}
                    usePortal={false}
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
        </ExportWrapper>
    );
};

export default RecommendationEngine;
