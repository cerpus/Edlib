import { useEffect, useState } from 'react';
import request from '../../helpers/request';
import useConfig from '../useConfig';

const useMaintenanceMode = () => {
    const [enabled, setEnabled] = useState(false);
    const [error, setError] = useState(false);
    const [loading, setLoading] = useState(false);
    const { edlib } = useConfig();

    useEffect(async () => {
        try {
            setEnabled(await request(edlib('/common/maintenance_mode'), 'GET'));
            setLoading(false);
        } catch (e) {
            setError(true);
        }
    }, []);

    return {
        enabled,
        error,
        loading,
    };
};

export default useMaintenanceMode;
