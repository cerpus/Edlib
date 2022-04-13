import React, { useState, useEffect } from 'react';
import {
    Button,
    TextField,
    Grid,
    Table,
    TableContainer,
    TableBody,
    TableRow,
    TableCell,
    Paper,
    Alert,
} from '@mui/material';
import useTranslation from '../../hooks/useTranslation';
import styled from 'styled-components';
import { ImagePreview } from './';
import FileSize from './FileSize';

const FileSelector = styled.input`
    postition: absolute;
    height: 1px;
    width: 1px;
    overflow: hidden;
    clip: rect(1px 1px 1px 1px);
`;

export default ({ onInsert, currentData }) => {
    const { t } = useTranslation();
    const [selectedImage, setSelectedImage] = useState(null);
    const [imageMeta, setImageMeta] = useState({});
    const [showOptimiseAlert, setShowOptimiseAlert] = useState(false);

    useEffect(() => {
        if (currentData) {
            setSelectedImage(currentData.file);
            setImageMeta(currentData.metadata);
        }
    }, [currentData]);

    const handleInput = (e) => {
        if (e.target.files.length === 1) {
            const file = e.target.files[0];
            setSelectedImage(file);
            setImageMeta({
                ...imageMeta,
                name: file.name,
                fileSize: file.size,
                lastModified: file.lastModified,
                type: file.type,
            })
            setShowOptimiseAlert(file.size >= 512000);
        }
    }

    const handleMetaChange = (e) => {
        setImageMeta({...imageMeta, [e.currentTarget.name]: e.currentTarget.value});
    };

    const imageLoaded = (data) => {
        setImageMeta({...imageMeta, ...data})
    };

    return (
        <Grid container spacing={2} sx={{p: '10px'}}>
            <Grid item container xs={12} spacing={4}>
                <Grid item>
                    <Button
                        variant="contained"
                        component="label"
                    >
                        {t('Select image')}
                        <FileSelector
                            id="imageSelector"
                            type="file"
                            accept="image/*"
                            onInput={handleInput}
                        />
                    </Button>
                </Grid>
                <Grid item>
                    <Button
                        variant="outlined"
                        onClick={() => onInsert({file: selectedImage, metadata: imageMeta})}
                        disabled={selectedImage === null}
                        color="success"
                    >
                        {t(currentData ? 'Update' : 'Sett inn')}
                    </Button>
                </Grid>
                {currentData && (
                    <Grid item>
                        <Button
                            variant="outlined"
                            onClick={() => onInsert(null)}
                            color="secondary"
                        >
                            {t('Cancel')}
                        </Button>
                    </Grid>
                )}
            </Grid>
            {showOptimiseAlert && (
                <Alert severity="warning" onClose={() => setShowOptimiseAlert(false)}>
                    This image exceeds the recommended filesize of 500 kB. Large files takes longer to load and
                    causes a degraded user experience. You should consider optimising the image unless a high
                    quality image is required.
                    The image can be optimised by using a different format, higher compression or
                    redusing the resolution.
                </Alert>
            )}
            {selectedImage !== null && (
                <Grid item container flexWrap="nowrap" spacing={2}>
                    <Grid item alignSelf="center" textAlign="center" sm={12} md={6}>
                        <ImagePreview file={selectedImage} afterLoad={imageLoaded} />
                    </Grid>
                    {imageMeta?.name && (
                        <Grid item container flexWrap="nowrap" flexDirection="column" spacing={2} sm={12} md={6}>
                            <Grid item flexWrap="nowrap" flexDirection="row">
                                <TableContainer component={Paper}>
                                    <Table size="small">
                                        <TableBody sx={{whiteSpace: 'nowrap'}}>
                                            <TableRow>
                                                <TableCell variant="head">{t('Navn')}</TableCell>
                                                <TableCell>{imageMeta.name ?? ''}</TableCell>
                                            </TableRow>
                                            <TableRow>
                                                <TableCell variant="head">{t('Filesize')}</TableCell>
                                                <TableCell><FileSize value={imageMeta.fileSize ?? '?'}/></TableCell>
                                            </TableRow>
                                            <TableRow>
                                                <TableCell variant="head">{t('Size (W x H)')}</TableCell>
                                                <TableCell>{imageMeta.width ?? '?'}px x {imageMeta.height ?? '?'}px</TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </TableContainer>
                            </Grid>
                            <Grid item flexWrap="nowrap" flexDirection="row">
                                <TableContainer component={Paper}>
                                    <Table size="small">
                                        <TableBody sx={{whiteSpace: 'nowrap'}}>
                                            <TableRow>
                                                <TableCell variant="head">{t('AltText')}</TableCell>
                                                <TableCell>
                                                    <TextField
                                                        id="altText"
                                                        name="altText"
                                                        variant="standard"
                                                        fullWidth
                                                        onChange={handleMetaChange}
                                                        value={imageMeta.altText ?? ''}
                                                    />
                                                </TableCell>
                                            </TableRow>
                                            <TableRow>
                                                <TableCell variant="head">{t('Caption')}</TableCell>
                                                <TableCell>
                                                    <TextField
                                                        id="caption"
                                                        name="caption"
                                                        variant="standard"
                                                        fullWidth
                                                        onChange={handleMetaChange}
                                                        value={imageMeta.caption ?? ''}
                                                    />
                                                </TableCell>
                                            </TableRow>
                                            <TableRow>
                                                <TableCell variant="head">{t('Photographer')}</TableCell>
                                                <TableCell>
                                                    <TextField
                                                        id="photographer"
                                                        name="photographer"
                                                        variant="standard"
                                                        fullWidth
                                                        onChange={handleMetaChange}
                                                        value={imageMeta.photographer ?? ''}
                                                    />
                                                </TableCell>
                                            </TableRow>
                                            <TableRow>
                                                <TableCell variant="head">{t('License')}</TableCell>
                                                <TableCell>
                                                    <TextField
                                                        id="license"
                                                        name="license"
                                                        variant="standard"
                                                        fullWidth
                                                        onChange={handleMetaChange}
                                                        value={imageMeta.license ?? ''}
                                                    />
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </TableContainer>
                            </Grid>
                        </Grid>
                    )}
                </Grid>
            )}
        </Grid>
    );
};
