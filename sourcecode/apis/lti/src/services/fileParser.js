import fs from 'fs';
import path from 'path';
import yaml from 'js-yaml';

export default {
    getConfigurationValuesFromSetupFile: (fileName) => {
        const file = path.resolve('/api-config', fileName);

        if (!fs.existsSync(file)) {
            return {};
        }

        return yaml.load(fs.readFileSync(file, 'utf8'));
    },
};
