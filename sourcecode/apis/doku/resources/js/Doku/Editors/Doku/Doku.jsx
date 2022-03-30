import React from 'react';
import { Breadcrumb, BreadcrumbItem } from '@cerpus/ui';
import Button from '@material-ui/core/Button';
import styled from 'styled-components';
import DokuComponent, {
    addEdlibResource,
    decorators,
} from '../../index';
import useSaveDoku from '../../hooks/requests/useSaveDoku';
import {
    FromSideModal,
    FromSideModalHeader,
} from '../../components/FromSideModal';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibResource } from '../../hooks/requests/useResource';
import PublishModal from './components/PublishModal';
import Contributors from './components/Contributors';
import { licenses } from './components/PublishModal/PublishModal';
import {
    createFromRaw,
    createEmptyEditorState,
} from '../../draftJSHelpers/createEditorState.js';

const Page = styled.div`
    display: flex;
    height: 100vh;
    flex-direction: column;
`;

const Content = styled.div`
    display: flex;
    flex: 1 1 100%;
    overflow-y: auto;
    padding: 15px 50px 15px 5px;

    > *:first-child {
        flex: 1;
    }
`;

const DokuWrapper = styled.div`
    max-height: 100%;
`;

const PreviewDokuWrapper = styled.div`
    overflow-y: auto;
`;

const Header = styled.div`
    flex: 0 0 auto;
    display: flex;
    justify-content: space-between;
    padding: 15px 50px;
    border-bottom: 1px solid #83df66;
`;

const Title = styled.input`
    font-weight: bold;
    font-size: 1.3em;
    margin: 5px 0;
    border: 0;
`;

const Buttons = styled.div`
    display: flex;
    flex-direction: row-reverse;
`;

const Status = styled.div`
    margin-bottom: 5px;
`;

const StoredStatus = styled.span`
    color: #545454;
    margin-right: 10px;
`;

const Doku = ({ doku }) => {
    const { t } = useTranslation();
    const [dokuData, _setDokuData] = React.useState(
        doku
            ? {
                  title: doku.title,
                  data: createFromRaw(doku.data),
              }
            : {
                  title: '',
                  data: createEmptyEditorState(decorators),
              }
    );
    const [license, setLicense] = React.useState(
        doku && doku.license ? doku.license : licenses.PRIVATE
    );
    const [showPreview, setShowPreview] = React.useState(false);
    const [hasBeenModified, setHasBeenModified] = React.useState(false);
    const [showPublishModal, setShowPublishModal] = React.useState(false);
    const [showContributorsModal, setShowContributorsModal] = React.useState(
        false
    );
    const setDokuData = React.useCallback(
        (data) => {
            _setDokuData({
                ...dokuData,
                ...data,
            });
            setHasBeenModified(true);
        },
        [dokuData, _setDokuData]
    );

    const useResource = useEdlibResource();

    const { currentId, savedDoku, publish, unpublish } = useSaveDoku(
        dokuData,
        license,
        doku,
        hasBeenModified
    );

    const isPublished = savedDoku && !savedDoku.isDraft;

    return (
        <Page>
            <Header>
                <div>
                    <Breadcrumb>
                        <BreadcrumbItem to="/test">Edlib</BreadcrumbItem>
                        <BreadcrumbItem to="/my-content" active>
                            {t('Mitt innhold')}
                        </BreadcrumbItem>
                    </Breadcrumb>
                    <Title
                        onChange={(e) =>
                            setDokuData({
                                title: e.target.value,
                            })
                        }
                        value={dokuData.title}
                        placeholder="Untitled doku"
                    />
                </div>
                <div>
                    <Status>
                        <StoredStatus>{t('Alle endringer er lagret')}</StoredStatus>{' '}
                        {isPublished ? (
                            <strong>{t('Publisert')}</strong>
                        ) : (
                            <strong>{t('Utkast')}</strong>
                        )}
                    </Status>
                    <Buttons>
                        {!isPublished && (
                            <Button
                                size="large"
                                disabled={!currentId}
                                onClick={() => {
                                    if (!currentId) {
                                        return;
                                    }
                                    publish().then(() =>
                                        setShowPublishModal(true)
                                    );
                                }}
                            >
                                {t('Publiser')}
                            </Button>
                        )}
                        {isPublished && (
                            <Button
                                size="large"
                                disabled={!currentId}
                                onClick={() => {
                                    if (!currentId) {
                                        return;
                                    }

                                    setShowPublishModal(true);
                                }}
                            >
                                {t('Publiseringsinnstillinger')}
                            </Button>
                        )}
                        <Button
                            style={{ marginRight: 5 }}
                            type="tertiary"
                            size="large"
                            onClick={() => setShowPreview(true)}
                        >
                            {t('Forhåndsvis')}
                        </Button>
                    </Buttons>
                </div>
            </Header>
            <Content>
                <DokuWrapper>
                    <DokuComponent
                        editorState={dokuData.data}
                        setEditorState={(data) => setDokuData({ data })}
                    />
                </DokuWrapper>
            </Content>
            <FromSideModal
                isOpen={showPreview}
                onClose={() => setShowPreview(false)}
                usePortal={false}
            >
                {showPreview && (
                    <div
                        style={{
                            display: 'flex',
                            flexDirection: 'column',
                            height: '100%',
                        }}
                    >
                        <FromSideModalHeader
                            onClose={() => setShowPreview(false)}
                        >
                            {t('Forhåndsvisning')}
                        </FromSideModalHeader>
                        <PreviewDokuWrapper>
                            <DokuComponent editorState={dokuData.data} />
                        </PreviewDokuWrapper>
                    </div>
                )}
            </FromSideModal>
            <PublishModal
                show={showPublishModal}
                setShow={setShowPublishModal}
                dokuId={currentId}
                license={license}
                setLicense={(...args) => {
                    setLicense(...args);
                    setHasBeenModified(true);
                }}
                unpublish={() => unpublish()}
            />
            <Contributors
                show={showContributorsModal}
                setShow={setShowContributorsModal}
            />
        </Page>
    );
};

export default Doku;
