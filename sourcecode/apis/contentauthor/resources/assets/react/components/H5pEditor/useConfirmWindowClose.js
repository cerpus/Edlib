import React from 'react';

export default (shouldConfirmClose) => {
    React.useEffect(() => {
        const onBeforeUnload = (e) => {
            if (shouldConfirmClose()) {
                e.preventDefault();
                e.returnValue = '';
            } else {
                delete e.returnValue;
            }
        };

        window.addEventListener('beforeunload', onBeforeUnload);

        return () => window.removeEventListener('beforeunload', onBeforeUnload);
    }, [shouldConfirmClose]);
};
