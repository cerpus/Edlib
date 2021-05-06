import React from 'react';
import styled from 'styled-components';
import useTranslation from '../../../hooks/useTranslation';
import Resource from './Resource';

const StyledResources = styled.div`
    flex: 1;
    border-bottom: 2px solid ${(props) => props.theme.colors.border};
`;

const defaultRowWrapper = ({ children }) => children();

const Resources = ({
    resources,
    rowWrapper = defaultRowWrapper,
    setSelectedResource,
}) => {
    const { t } = useTranslation();

    return (
        <StyledResources>
            {resources.length === 0 && t('Fant ingen ressurser')}
            {resources.map((resource, index) =>
                rowWrapper({
                    resourceId: resource.edlibId,
                    resource,
                    index,
                    children: () => (
                        <Resource
                            key={resource.edlibId}
                            resource={resource}
                            onPreview={() => setSelectedResource(resource)}
                        />
                    ),
                })
            )}
        </StyledResources>
    );
};

export default Resources;
