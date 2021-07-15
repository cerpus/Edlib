import React from 'react';
import {
    HeaderDropDownLink,
    Header,
    HeaderLink,
    HeaderLinkGroup,
    HeaderLogo,
    HeaderToggler,
} from '@cerpus/ui';
import {
    AddCircleRounded,
    Home,
    ShoppingCart,
    Close,
} from '@material-ui/icons';
import styled from 'styled-components';
import { useLocation, matchPath } from 'react-router-dom';
import { useConfigurationContext } from '../contexts/Configuration';
import useTranslation from '../hooks/useTranslation';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import resourceEditors from '../constants/resourceEditors';

const StyledClose = styled.div`
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
    padding-right: 15px;
    color: white;

    & > svg {
        width: 40px;
        height: 40px;
        cursor: pointer;
    }
`;

export default ({ onClose, viewportHeight }) => {
    const { t } = useTranslation();
    const [isExpanded, setExpanded] = React.useState(false);
    const location = useLocation();
    const { enableDoku } = useConfigurationContext();
    const { getUserConfig } = useEdlibComponentsContext();

    const isActive = (path) => {
        let paths = [path];

        if (Array.isArray(path)) {
            paths = [...path];
        }

        return paths.some((path) =>
            matchPath(location.pathname, {
                path,
                exact: false,
            })
        );
    };

    const enabledTypes =
        getUserConfig('enabledResourceTypes') || Object.values(resourceEditors);

    const isEditorEnabled = (type) => enabledTypes.indexOf(type) !== -1;

    const editorMapping = {
        [resourceEditors.H5P]: {
            link: '/resources/new/contentauthor?group=h5p',
            label: t('Interaktivitet'),
        },
        [resourceEditors.QUESTION_SET]: {
            link: '/resources/new/contentauthor?group=questionset',
            label: t('Spørsmål'),
        },
        [resourceEditors.ARTICLE]: {
            link: '/resources/new/contentauthor?group=article',
            label: t('Tekst'),
        },
        // [resourceEditors.EMBED]: {
        //     link: '/resources/new/url',
        //     label: 'Link',
        // },
        [resourceEditors.DOKU]: {
            link: '/resources/new/doku',
            label: 'EdStep',
        },
    };

    const activatedEditorsList = Object.entries(editorMapping)
        .filter(([type]) => isEditorEnabled(type))
        .filter(([type]) => {
            switch (type) {
                case resourceEditors.DOKU:
                    return enableDoku;
                default:
                    return true;
            }
        });

    return (
        <Header viewportHeight={viewportHeight}>
            <HeaderToggler onToggle={() => setExpanded(!isExpanded)} />
            <HeaderLogo />
            <HeaderLinkGroup
                isExpanded={isExpanded}
                onClose={() => setExpanded(false)}
            >
                {activatedEditorsList.length > 1 && (
                    <HeaderDropDownLink
                        link={
                            <HeaderLink
                                LogoComponent={AddCircleRounded}
                                active={isActive([
                                    '/resources/new',
                                    '/link-author',
                                    '/doku-author',
                                ])}
                            >
                                {t('Opprett innhold')}
                            </HeaderLink>
                        }
                    >
                        {activatedEditorsList.map(([type, { link, label }]) => (
                            <HeaderLink to={link} key={type}>
                                {label}
                            </HeaderLink>
                        ))}
                    </HeaderDropDownLink>
                )}
                {activatedEditorsList.length === 1 && (
                    <HeaderLink
                        LogoComponent={AddCircleRounded}
                        to={activatedEditorsList[0][1].link}
                        active={isActive(activatedEditorsList[0][1].link)}
                    >
                        {t('Opprett innhold')}
                    </HeaderLink>
                )}
                <HeaderLink
                    LogoComponent={ShoppingCart}
                    to="/shared-content"
                    active={isActive('/shared-content')}
                >
                    {t('Delt innhold')}
                </HeaderLink>
                <HeaderLink
                    LogoComponent={Home}
                    to="/my-content"
                    active={isActive('/my-content')}
                >
                    {t('Mitt innhold')}
                </HeaderLink>
            </HeaderLinkGroup>
            {onClose ? (
                <StyledClose>
                    <Close onClick={onClose} />
                </StyledClose>
            ) : (
                <div>&nbsp</div>
            )}
        </Header>
    );
};
