import React from 'react';

export default (actionFnc) => {
    const [status, setStatus] = React.useState({
        error: false,
        loading: false,
        success: false,
    });

    return {
        status,
        action: (variables, clb) => {
            setStatus({
                error: false,
                success: false,
                loading: true,
            });
            actionFnc(variables)
                .then((response) => {
                    setStatus({
                        error: false,
                        success: true,
                        loading: false,
                    });
                    clb && clb(response);
                })
                .catch((error) => {
                    console.error(error);
                    setStatus({
                        error,
                        success: false,
                        loading: false,
                    });
                });
        },
    };
};
