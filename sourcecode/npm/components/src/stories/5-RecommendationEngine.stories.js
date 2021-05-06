import React from 'react';
import { Input, FormGroup, Label } from '@cerpus/ui';
import store from 'store';
import RecommendationEngineComponent from '../exportedComponents/RecommendationEngine';
import { EdlibComponentsProvider } from '..';

export default {
    title: 'RecommendationEngine',
};

export const RecommendationEngine = () => {
    const [token, setToken] = React.useState(() => {
        const storedToken = store.get('token');
        return storedToken ? storedToken : '';
    });

    React.useEffect(() => {
        store.set('token', token);
    }, [token]);

    return (
        <EdlibComponentsProvider
            coreUrl="http://core:8106"
            edlibUrl="http://api.edlib.local"
            getJwt={async () => {
                return token;
            }}
        >
            <div style={{ width: 500, paddingLeft: 20, height: 400 }}>
                <FormGroup>
                    <Label>Token</Label>
                    <Input
                        value={token}
                        onChange={(e) => setToken(e.target.value)}
                    />
                </FormGroup>
                <RecommendationEngineComponent
                    token={token}
                    useV2={true}
                    showDragIcon
                />
            </div>
        </EdlibComponentsProvider>
    );
};
