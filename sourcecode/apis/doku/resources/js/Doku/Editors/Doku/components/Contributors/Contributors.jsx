import React from 'react';
import {
    FromSideModal,
    FromSideModalHeader,
    FromSideModalBody,
} from '../../../../components/FromSideModal';
import useTranslation from '../../../../hooks/useTranslation';
import {
    TableContainer,
    Table,
    TableHead,
    TableRow,
    TableCell,
    TableBody,
    Radio,
    Box,
} from '@mui/material';
import { Person as PersonIcon } from '@mui/icons-material';

const Contributors = ({ show, setShow }) => {
    const { t } = useTranslation();

    return (
        <FromSideModal
            isOpen={show}
            onClose={() => setShow(false)}
        >
            {show && (
                <>
                    <FromSideModalHeader onClose={() => setShow(false)}>
                        {t('Samarbeidere')}
                    </FromSideModalHeader>
                    <FromSideModalBody>
                        <TableContainer>
                            <Table aria-label="simple table">
                                <TableHead>
                                    <TableRow>
                                        <TableCell />
                                        <TableCell align="center">
                                            {t('Forfatter')}
                                        </TableCell>
                                        <TableCell align="center">
                                            {t('Samarbeider')}
                                        </TableCell>
                                        <TableCell align="center">
                                            {t('Ingen tilgang')}
                                        </TableCell>
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {[
                                        {
                                            email: 'max.moeschinger@cerpus.com',
                                            author: true,
                                        },
                                        {
                                            email: 'm.moeschinger@gmail.com',
                                            collaborator: true,
                                        },
                                    ].map((row, index) => (
                                        <TableRow key={index}>
                                            <TableCell scope="row">
                                                <Box display="flex">
                                                    <Box>
                                                        <PersonIcon fontSize="small" />
                                                    </Box>
                                                    <Box>{row.email}</Box>
                                                </Box>
                                            </TableCell>
                                            <TableCell
                                                scope="row"
                                                align="center"
                                            >
                                                <Radio
                                                    color="default"
                                                    checked={row.author}
                                                />
                                            </TableCell>
                                            <TableCell
                                                scope="row"
                                                align="center"
                                            >
                                                <Radio
                                                    color="default"
                                                    checked={row.collaborator}
                                                />
                                            </TableCell>
                                            <TableCell
                                                scope="row"
                                                align="center"
                                            >
                                                <Radio
                                                    color="default"
                                                    checked={false}
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </TableContainer>
                    </FromSideModalBody>
                </>
            )}
        </FromSideModal>
    );
};

export default Contributors;
