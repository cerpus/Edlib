import React from 'react';
import styled from 'styled-components';
import { Button, TextField, Alert, Typography, CircularProgress } from '@mui/material';
import DokuComponent, { addEdlibResource, decorators } from '../../index';
import useSaveDoku from '../../hooks/requests/useSaveDoku';
import { FromSideModal, FromSideModalHeader } from '../../components/FromSideModal';
import useTranslation from '../../hooks/useTranslation';
import { useEdlibResource } from '../../hooks/requests/useResource';
import PublishModal from './components/PublishModal';
import Contributors from './components/Contributors';
import { licenses } from './components/PublishModal/PublishModal';
import { createFromRaw, createEmptyEditorState } from '../../draftJSHelpers/createEditorState.js';

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

const TitleContainer = styled.div`
    flex: 1 1 100%;
    display: flex;
    margin-right: 50px;
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
    display: flex;
    flex-wrap: nowrap;
`;

const StoredStatus = styled.span`
    color: #545454;
    margin-right: 10px;
    flex: 1 1 100%;
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

    const { currentId, savedDoku, publish, unpublish, error, saving } = useSaveDoku(
        dokuData,
        license,
        doku,
        hasBeenModified
    );

    const renderStatus = () => {
        const style = {pt:0, pb: 0};

        if (saving) {
            return (
                <Alert
                    severity="info"
                    sx={style}
                    icon={<CircularProgress variant="indeterminate" size="20px" />}
                >
                    {t('Lagrer')}
                </Alert>
            );
        } else if (error) {
            return (
                <Alert
                    severity="error"
                    sx={style}
                >
                    {t('Lagring feilet')}
                </Alert>
            );
        } else if (savedDoku) {
            return (
                <Alert
                    severity="success"
                    sx={style}
                >
                    {t('Alle endringer er lagret')}
                </Alert>
            );
        }

        return '';
    };

    const isPublished = savedDoku && !savedDoku.isDraft;

    return (
        <Page>
            <Header>
                <TitleContainer>
                    <TextField
                        id="title-input"
                        label={t('Title')}
                        value={dokuData.title}
                        onChange={(e) =>
                            setDokuData({title: e.target.value})
                        }
                        placeholder="Untitled doku"
                        variant="standard"
                        inputProps={{
                            sx: {
                                fontWeight: 'bold',
                                fontSize: '1.3em',
                            }
                        }}
                        fullWidth={true}
                    />
                </TitleContainer>
                <div>
                    <Status>
                        <StoredStatus>
                            {renderStatus()}
                        </StoredStatus>
                        {' '}
                        <Typography
                            variant="h5"
                            sx={{m: '2px 0'}}
                        >
                            {isPublished ? t('Publisert') :t('Utkast')}
                        </Typography>
                    </Status>
                    <Buttons>
                        {!isPublished && (
                            <Button
                                variant="outlined"
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
                                variant="outlined"
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
                            variant="outlined"
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
