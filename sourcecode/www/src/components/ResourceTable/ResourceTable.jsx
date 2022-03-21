import React from 'react';
import styled from 'styled-components';
import _ from 'lodash';
import ResourceEditCog from '../ResourceEditCog';
import License from '../License';
import { getResourceName, ResourceIcon } from '../Resource';
import useTranslation from '../../hooks/useTranslation';
import moment from 'moment';
import { useEdlibComponentsContext } from '../../contexts/EdlibComponents';
import resourceColumns from '../../constants/resourceColumns';
import PublishedTag from '../PublishedTag';
import { iso6393ToString } from '../../helpers/language.js';
import ViewContainer from '../ResourcePage/components/ViewContainer.jsx';
import ClickableHeader from './ClickableHeader.jsx';

const Row = styled.div`
    display: grid;
    grid-template-columns: [icon] 80px [title] minmax(0, 1fr) [date] 100px [author] 100px [language] 160px [status] 130px [views] 90px [license] 80px [actions] 60px;
`;

const BodyRow = styled(Row)`
    box-shadow: 0 0 5px 6px rgba(0, 0, 0, 0.04);
    background-color: white;
    margin-bottom: 20px;
    cursor: pointer;
    min-height: 72px;
    transition: box-shadow 0.01s ease-in-out;

    &:hover {
        box-shadow: 0 0 5px 6px rgba(0, 0, 0, 0.1);
    }
`;

const HeaderRow = styled(Row)`
    font-weight: bold;
    margin-bottom: 8px;
    font-size: ${(props) => props.theme.rem(0.8)};

    & > div {
        padding: 0 5px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
`;

const Cell = styled.div`
    padding: 0 5px;
    ${(props) =>
        props.name &&
        `
        grid-column-start: ${props.name};
  `}
    ${(props) =>
        props.vc &&
        `
        display: flex;
        flex-direction: column;
        justify-content: center;
  `}
    ${(props) =>
        props.secondary &&
        `
        color: #595959;
        font-size: ${props.theme.rem(0.8)};
  `}
`;

const ImageCell = styled(Cell)`
    padding: 0;

    img {
        max-width: 100%;
    }
`;

const ResourceIconCell = styled(Cell)`
    display: flex;
`;

const Title = styled.div`
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-right: 20px;
    font-size: ${(props) => props.theme.rem(1.2)};
`;

const UnderTitle = styled.div`
    font-size: ${(props) => props.theme.rem(0.8)};
`;

const CogWrapper = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    flex: 1;
`;

const ResourceTable = ({
    totalCount,
    resources,
    showDeleteButton = false,
    sortingOrder,
    setSortingOrder,
    refetch,
}) => {
    const { t } = useTranslation();
    const { getUserConfig } = useEdlibComponentsContext();
    const hideResourceColumns = getUserConfig('hideResourceColumns');

    return (
        <ViewContainer
            showDeleteButton={showDeleteButton}
            refetch={refetch}
            resources={resources}
        >
            {({ cogProps, setSelectedResource, resources }) => (
                <>
                    <HeaderRow>
                        <div
                            style={{
                                gridColumnStart: 'icon',
                                gridColumnEnd: 'span date',
                            }}
                        >
                            <div>
                                {t('Innhold')}{' '}
                                <span
                                    style={{
                                        fontWeight: 'normal',
                                    }}
                                >
                                    <i>{`${totalCount} ${t('ressurser')}`}</i>
                                </span>
                            </div>
                        </div>
                        <ClickableHeader
                            sortingOrder={sortingOrder}
                            setSortingOrder={setSortingOrder}
                            name="updatedAt"
                        >
                            {_.capitalize(t('last_changed'))}
                        </ClickableHeader>
                        <div>{_.capitalize(t('author'))}</div>
                        <div>{_.capitalize(t('language'))}</div>
                        <div>{_.capitalize(t('status'))}</div>
                        <ClickableHeader
                            sortingOrder={sortingOrder}
                            setSortingOrder={setSortingOrder}
                            name="views"
                        >
                            {_.capitalize(t('view', { count: 2 }))}
                        </ClickableHeader>
                        {hideResourceColumns.indexOf(
                            resourceColumns.LICENSE
                        ) === -1 && (
                            <div>
                                {_.capitalize(t('license', { count: 2 }))}
                            </div>
                        )}
                        <div />
                    </HeaderRow>
                    {resources.map((resource) => (
                        <BodyRow
                            onClick={() => setSelectedResource(resource)}
                            key={resource.id}
                        >
                            <ImageCell vc>
                                <ResourceIcon
                                    contentTypeInfo={resource.contentTypeInfo}
                                    resourceVersion={resource.version}
                                    fontSizeRem={2}
                                />
                            </ImageCell>
                            <Cell vc>
                                <Title>{resource.version.title}</Title>
                                <UnderTitle>
                                    {getResourceName(resource)}
                                </UnderTitle>
                            </Cell>
                            <Cell vc secondary>
                                {moment(resource.version.updatedAt).format(
                                    'D. MMM YY'
                                )}
                            </Cell>
                            <Cell vc secondary>
                                {resource.version.authorOverwrite}
                            </Cell>
                            <Cell vc secondary>
                                {iso6393ToString(resource.version.language)}
                            </Cell>
                            <Cell vc secondary>
                                <PublishedTag
                                    isPublished={resource.version.isPublished}
                                    isDraft={resource.version.isDraft}
                                />
                            </Cell>
                            <Cell vc secondary>
                                {resource.analytics.viewCount !== 0
                                    ? resource.analytics.viewCount
                                    : ''}
                            </Cell>
                            {hideResourceColumns.indexOf(
                                resourceColumns.LICENSE
                            ) === -1 && (
                                <Cell vc secondary>
                                    <License
                                        license={resource.version.license}
                                    />
                                </Cell>
                            )}
                            <ResourceIconCell
                                className="actions"
                                secondary
                                name="actions"
                            >
                                <CogWrapper>
                                    <ResourceEditCog {...cogProps(resource)} />
                                </CogWrapper>
                            </ResourceIconCell>
                        </BodyRow>
                    ))}
                </>
            )}
        </ViewContainer>
    );
};

export default ResourceTable;
