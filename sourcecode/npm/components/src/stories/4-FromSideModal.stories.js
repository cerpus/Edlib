import React from 'react';
import {
    FromSideModal as ActualFromSideModal,
    FromSideModalHeader,
} from '../components/FromSideModal';
import { Button } from '@cerpus/ui';

export default {
    title: 'FromSideModal',
};

export const FromSideModal = () => {
    const [isOpen, setIsOpen] = React.useState(false);
    return (
        <div>
            <Button onClick={() => setIsOpen(true)}>open</Button>
            <ActualFromSideModal
                isOpen={isOpen}
                onClose={() => setIsOpen(false)}
            >
                <FromSideModalHeader onClose={() => setIsOpen(false)}>
                    test
                </FromSideModalHeader>
            </ActualFromSideModal>
        </div>
    );
};
